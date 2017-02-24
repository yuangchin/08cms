<?php
class Progress{
	function Progress($str = ''){
		$this->__construct($str);
	}

	function __construct($str = ''){
		echo '<script type="text/javascript" src="include/js/progress.js"></script><script type="text/javascript">var progress = new Progress("' . str_replace(array("\\", "\r", "\n", '"'), array("\\\\", "\\r", "\\n", '\"'), $str) . '")</script>';
		ob_implicit_flush();
	}
	
	function rate($rate){
		echo '<script type="text/javascript">progress.rate(' . $rate . ')</script>';
	}

	function show(){
		echo '<script type="text/javascript">progress.show()</script>';
	}

	function hide(){
		echo '<script type="text/javascript">progress.hide()</script>';
	}
	
	function pagecount($num){
		echo "<script type=\"text/javascript\">progress.pagecount($num)</script>";
	}
	
	function linkcount($num){
		echo "<script type=\"text/javascript\">progress.linkcount($num)</script>";
	}
	
	function content($num){
		echo "<script type=\"text/javascript\">progress.content($num)</script>";
	}
	
	function output($num){
		echo "<script type=\"text/javascript\">progress.output($num)</script>";
	}
}
?>