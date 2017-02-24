<?php
#ini_set('date.timezone','ETC/GMT-8');
#set_time_limit(0);
class PHPzip{
	var $error,$errno;
	var $fp,$level,$zfile;
	var $offset=0,$files=0,$dirs=Array('./'=>1,'../'=>1);
	var $output,$sendedheader;
	function PHPzip($file='PHPzip.zip',$level=9){
		$this->zfile=$file;
		if(!is_numeric($level)||$level<0||$level>9)$level=9;
		$this->level=$level;
	}
	function open($mode='r'){
		$this->close();
		$this->fp=fopen($this->zfile,$mode);
	}
	function close(){
		if(isset($this->fp)){fclose($this->fp);unset($this->fp);}
	}
	function filelist($file=''){
		if($file)$this->zfile=$file;
		if(!is_file($this->zfile))return $this->seterr(101);
		$this->open();
		$ret=array();
		$ctd=$this->ReadCentralDir();
		fseek($this->fp,$ctd['offset']);
		for($i=0;$i<$ctd['entries'];$i++){
			$CFH=$this->ReadCentralFileHeaders();
			$info['index'] =$i;
			$info['name']=$CFH['filename'];
			$info['size']=$CFH['size'];
			$info['compressed_size']=$CFH['compressed_size'];
			$info['time']=$CFH['mtime'];
			$info['crc']=strtoupper(dechex($CFH['crc']));
			$info['folder']=($CFH['external']==0x41FF0010||$CFH['external']==16)?1:0;
			$ret[]=$info;
		}
		$this->close();
		return $ret;
	}
	function add($file,$name=''){
		if(is_dir($file)){
			$fp=opendir($file);
			if($name&&'/'!=substr($name,-1))$name.='/';
			while(($sub=readdir($fp))!==false)if($sub!='.'&&$sub!='..')$this->add($file.'/'.$sub,$name.$sub);
			closedir($fp);
		}elseif(is_file($file)){
			$data=file_get_contents($file);
			if(!$name)
				$name=basename($file);
			elseif(strpos($name,'/')){
				$dir=dirname($name).'/';
				if(!isset($this->dirs[$dir]))$this->adddir($dir,filemtime(dirname($file)));
			}
			$this->adddata($name,$data,filemtime($file));
		}else return $this->seterr(301,$file);
	}
	function adddir($name,$time=0){
		if(!$this->fp)return;
		$tp=pack("VVVVvv",$this->Unix2DosTime($time),0,0,0,strlen($name),0);
		$data=pack('Vvvv',67324752,10,0,0).$tp.$name.pack("VVV",0,0,0);
		$this->ctrl_dir.=pack('Vvvvv',33639248,20,10,0,0).$tp.pack("vvvVV",0,0,0,16,$this->offset).$name;
		$this->dispose($data);
		$this->offset+=strlen($data);
		$this->dirs[$name]=1;
		$this->files++;
	}
	function adddata($name,&$data,$time=0){
		$level=$this->level;
		$hexdtime=pack('V',$this->Unix2DosTime($time));
		$unc_len=strlen($data);$crc=crc32($data);
		if($level){
			$data=gzcompress($data,$level);
			$c_len=strlen($data)-6;
			$data=substr($data,2,$c_len);
			$fr=pack('Vvvv',67324752,14,0,8);
		}else{
			$c_len=$unc_len;
			$fr=pack('Vvvv',67324752,10,0,0);
		}
		$tp=pack('VVV',$crc,$c_len,$unc_len);
		$data=$fr.$hexdtime.$tp.pack('vv',strlen($name),0).$name.$data.$tp;
		$this->dispose($data);
		$this->ctrl_dir.=($level?pack('Vvvvv',33639248,0,14,0,8):pack('Vvvvv',33639248,14,10,0,0)).$hexdtime.$tp.pack('vvvvvVV',strlen($name),0,0,0,0,32,$this->offset).$name;
		$this->offset+=strlen($data);
		$this->files++;
		return 0;
	}
	function dispose(&$data){
		if($this->output){
			if(!$this->sendedheader){
				header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
				header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
				header("Pragma: no-cache");
				header('Content-Type: application/zip');
				header('Content-Disposition: attachment; filename="'.str_replace('"','&quot;',$this->zfile).'"');
				$this->sendedheader=true;
			}
			echo $data;
		}else{
			if(!$this->fp){
				if(is_file($this->zfile))return $this->seterr(201);
				$dir=dirname($this->zfile);
				if($dir&&!is_dir($dir))$this->mkdir($dir);
				$this->open('w');
			}
			fwrite($this->fp,$data);
		}
	}
	function create(){
		#压缩包结束信息,包括文件总数,目录信息读取指针位置等信息
		$endstr=$this->ctrl_dir.pack('VvvvvVVv',101010256,0,0,$this->files,$this->files,strlen($this->ctrl_dir),$this->offset,0);
		$this->dispose($endstr);
		$this->close();
	}
	function Extract($to='',$index=-1){
		if(!is_file($this->zfile))return $this->seterr(101);
		$this->open();
		$cdir=$this->ReadCentralDir();
		$pos=$cdir['offset'];
		if(!is_array($index))$index=array($index);
		for($i=0;isset($index[$i]);$i++){
			if(intval($index[$i])!=$index[$i]||$index[$i]>$cdir['entries'])return $this->seterr(401);
		}
		if($to&&substr($to,-1)!="/") $to.="/";
		for($i=0;$i<$cdir['entries'];$i++){
			fseek($this->fp,$pos);
			$CFH=$this->ReadCentralFileHeaders();
			$CFH['index']=$i;
			$pos=ftell($this->fp);
			if(in_array(-1,$index)||in_array($i,$index))$stat[$CFH['filename']]=$this->ExtractFile($CFH,$to);
		}
		$this->close();
		return $stat;
	}
	function ExtractFile($CFH,$to){
		$name=$to.$CFH['filename'];
		if(is_file($name))return $this->seterr(402,$name);
		fseek($this->fp,$CFH['offset']+30+$CFH['filename_len']);
		$pd=dirname($name);
		if($pd&&!is_dir($pd))$this->mkdir($pd);
		if($CFH['external']!=0x41FF0010&&$CFH['external']!=16){
			if($CFH['compression']==0){
				$fp=fopen($name,'w');
				$size=$CFH['compressed_size'];
				while($size>0){
					$read_size=$size>8192?8192:$size;
					fwrite($fp,pack('a'.$read_size,fread($this->fp,$read_size)),$read_size);
					$size-=$read_size;
				}
				fclose($fp);
			}else{
				$tmp=$temp=$name.'.gz';$i=0;
				while(is_file($tmp))$tmp=$temp.'.'.$i++;
				$fp=fopen($tmp,'w');
				fwrite($fp,pack('va1a1Va1a1',0x8b1f,chr($CFH['compression']),chr(0),time(),chr(0),chr(3)),10);
				$size=$CFH['compressed_size'];
				while($size>0){
					$read_size=$size>8192?8192:$size;
					fwrite($fp,pack('a'.$read_size,fread($this->fp,$read_size)),$read_size);
					$size-=$read_size;
				}
				fwrite($fp,pack('VV',$CFH['crc'],$CFH['size']),8);
				fclose($fp);
				$gzp=gzopen($tmp,'r');if(!$gzp)return $this->seterr(403,$to.$CFH['filename']);
				$fp=fopen($name,'w');
				$size = $CFH['size'];
				while($size>0){
					$read_size=$size>8192?8192:$size;
					fwrite($fp,pack('a'.$read_size,gzread($gzp,$read_size)),$read_size);
					$size-=$read_size;
				}
				fclose($fp);gzclose($gzp);
				unlink($tmp);
			}
			touch($name,$CFH['mtime']);
		}
		return true;
	}
	function Unix2DosTime($t=0){
		$t=explode(' ',date('Y n j G i s',($t==0)?time():$t));
		return (($t[0]-1980)<<25)|($t[1]<<21)|($t[2]<<16)|($t[3]<<11)|($t[4]<<5)|($t[5]>>1);
	}
	function ReadCentralDir(){
		if(!$this->fp)return;
		$size=filesize($this->zfile);
		$maximum_size=($size<277)?$size:277;
		fseek($this->fp,$size-$maximum_size);
		$pos=ftell($this->fp);$bytes=0x00000000;
		while($pos<$size){
			$byte=fread($this->fp,1);$bytes=($bytes<<8&0xffffffff)|ord($byte);$pos++;
			if($bytes==0x504b0506)break;
		}
		$ctd=@unpack('vdisk/vdisk_start/vdisk_entries/ventries/Vsize/Voffset/vcomment',fread($this->fp,18));
		if($ctd['comment']!=0)$ctd['comment']=fread($this->fp,$ctd['comment']);
		return $ctd;
	}
	function ReadCentralFileHeaders(){
		if(!$this->fp)return;
		$binary_data=fread($this->fp,46);
		$CFH=unpack('vchkid/vid/vversion/vversion_extracted/vflag/vcompression/vmtime/vmdate/Vcrc/Vcompressed_size/Vsize/vfilename_len/vextra_len/vcomment_len/vdisk/vinternal/Vexternal/Voffset',$binary_data);
		$CFH['filename']=$CFH['filename_len']?fread($this->fp,$CFH['filename_len']):'';
		$CFH['extra']=$CFH['extra_len']?fread($this->fp,$CFH['extra_len']):'';
		$CFH['comment']=$CFH['comment_len']?fread($this->fp,$CFH['comment_len']):'';
		if($CFH['mdate']&&$CFH['mtime']){
			$hour=($CFH['mtime']&0xF800)>>11;
			$minute=($CFH['mtime']&0x07E0)>>5;
			$seconde=($CFH['mtime']&0x001F)*2;
			$year=(($CFH['mdate']&0xFE00)>>9)+1980;
			$month=($CFH['mdate']&0x01E0)>>5;
			$day=$CFH['mdate']&0x001F;
			$CFH['mtime']=mktime($hour,$minute,$seconde,$month,$day,$year);
		}else{
			$CFH['mtime']=time();
		}
		$CFH['stored_filename']=$CFH['filename'];
		$CFH['status']='ok';
		if(substr($CFH['filename'],-1)=='/')$CFH['external']=0x41FF0010;
		return $CFH;
	}
	function mkdir($dir){
		$pd=dirname($dir);
		if(!is_dir($pd))$this->mkdir($pd);
		mkdir($dir,0777);
	}
	function seterr($no,$msg=''){
		$this->errno[]=$no;
		switch($no){
			case 101:$this->error[]="File $this->zfile does not exist.";break;
			case 201:$this->error[]="File $this->zfile exist.";break;
			case 301:$this->error[]="The add file $msg does not exist.";break;
			case 401:$this->error[]='Decompression index error.';break;
			case 402:$this->error[]="Create $msg failure.";break;
			case 403:$this->error[]="Open temporary file $msg failure.";break;
			default:$this->error[]='Unknown error.';
		}
		return $no;
	}
}
?>