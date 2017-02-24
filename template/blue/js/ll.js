lilv_array = new Array; 
//08年12月23日利率下限(7折)
lilv_array[1] = new Array;
lilv_array[1][1] = new Array;
lilv_array[1][2] = new Array;
lilv_array[1][1][5] = 0.0403;//商贷 1～5年 4.03%
lilv_array[1][1][10] = 0.0416;//商贷 5-30年 4.16%
lilv_array[1][2][5] = 0.0333;//公积金 1～5年 3.33%
lilv_array[1][2][10] = 0.0387;//公积金 5-30年 3.87%
//08年12月23日利率下限(85折)
lilv_array[2] = new Array;
lilv_array[2][1] = new Array;
lilv_array[2][2] = new Array;
lilv_array[2][1][5] = 0.049;//商贷 1～5年 4.9%
lilv_array[2][1][10] = 0.0505;//商贷 5-30年 5.05%
lilv_array[2][2][5] = 0.0333;//公积金 1～5年 3.33%
lilv_array[2][2][10] = 0.0387;//公积金 5-30年 3.87%
//08年12月23日基准利率
lilv_array[3] = new Array;
lilv_array[3][1] = new Array;
lilv_array[3][2] = new Array;
lilv_array[3][1][5] = 0.0576;//商贷 1～5年 5.76%
lilv_array[3][1][10] = 0.0594;//商贷 5-30年 5.94%
lilv_array[3][2][5] = 0.0333;//公积金 1～5年 3.33%
lilv_array[3][2][10] = 0.0387;//公积金 5-30年 3.87%
//08年12月23日利率上限(1.1倍)
lilv_array[4] = new Array;
lilv_array[4][1] = new Array;
lilv_array[4][2] = new Array;
lilv_array[4][1][5] = 0.0634;//商贷 1～5年 6.34%
lilv_array[4][1][10] = 0.0653;//商贷 5-30年 6.53%
lilv_array[4][2][5] = 0.0333;//公积金 1～5年 3.33%
lilv_array[4][2][10] = 0.0387;//公积金 5-30年 3.87%
//10年10月20日利率下限(7折)
lilv_array[5] = new Array;
lilv_array[5][1] = new Array;
lilv_array[5][2] = new Array;
lilv_array[5][1][5] = 0.04172;//商贷 1～5年 4.172%
lilv_array[5][1][10] = 0.04298;//商贷 5-30年 4.298%
lilv_array[5][2][5] = 0.035;//公积金 1～5年 3.5%
lilv_array[5][2][10] = 0.0405;//公积金 5-30年 4.05%
//10年10月20日利率下限(85折)
lilv_array[6] = new Array;
lilv_array[6][1] = new Array;
lilv_array[6][2] = new Array;
lilv_array[6][1][5] = 0.05066;//商贷 1～5年 5.066%
lilv_array[6][1][10] = 0.05218;//商贷 5-30年 5.218%
lilv_array[6][2][5] = 0.035;//公积金 1～5年 3.5%
lilv_array[6][2][10] = 0.0405;//公积金 5-30年 4.05%
//10年10月20日基准利率
lilv_array[7] = new Array;
lilv_array[7][1] = new Array;
lilv_array[7][2] = new Array;
lilv_array[7][1][5] = 0.0596;//商贷 1～5年 5.96%
lilv_array[7][1][10] = 0.0614;//商贷 5-30年 6.14%
lilv_array[7][2][5] = 0.035;//公积金 1～5年 3.5%
lilv_array[7][2][10] = 0.0405;//公积金 5-30年 4.05%
//10年10月20日利率上限(1.1倍)
lilv_array[8] = new Array;
lilv_array[8][1] = new Array;
lilv_array[8][2] = new Array;
lilv_array[8][1][5] = 0.06556;//商贷 1～5年 6.556%
lilv_array[8][1][10] = 0.06754;//商贷 5-30年 6.754%
lilv_array[8][2][5] = 0.035;//公积金 1～5年 3.5%
lilv_array[8][2][10] = 0.0405;//公积金 5-30年 4.05%
//10年12月26日基准利率
lilv_array[9] = new Array;
lilv_array[9][1] = new Array;
lilv_array[9][2] = new Array;
lilv_array[9][1][5] = 0.0622;//商贷 1～5年 6.22%
lilv_array[9][1][10] = 0.0640;//商贷 5-30年 6.4%
lilv_array[9][2][5] = 0.0375;//公积金 1～5年 3.75%
lilv_array[9][2][10] = 0.0430;//公积金 5-30年 4.3%
//10年12月26日利率下限(7折)
lilv_array[10] = new Array;
lilv_array[10][1] = new Array;
lilv_array[10][2] = new Array;
lilv_array[10][1][5] = 0.04354;//商贷 1～5年 4.354%
lilv_array[10][1][10] = 0.0448;//商贷 5-30年 4.48%
lilv_array[10][2][5] = 0.0375;//公积金 1～5年 3.75%
lilv_array[10][2][10] = 0.0430;//公积金 5-30年 4.3%
//10年12月26日利率上限(1.1倍)
lilv_array[11] = new Array;
lilv_array[11][1] = new Array;
lilv_array[11][2] = new Array;
lilv_array[11][1][5] = 0.06842;//商贷 1～5年 6.842%
lilv_array[11][1][10] = 0.0704;//商贷 5-30年 7.04%
lilv_array[11][2][5] = 0.0375;//公积金 1～5年 3.75%
lilv_array[11][2][10] = 0.0430;//公积金 5-30年 4.3%
//11年2月9日基准利率
lilv_array[12] = new Array;
lilv_array[12][1] = new Array;
lilv_array[12][2] = new Array;
lilv_array[12][1][5] = 0.0645;//商贷 1～5年 6.45%
lilv_array[12][1][10] = 0.0660;//商贷 5-30年 6.6%
lilv_array[12][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[12][2][10] = 0.0450;//公积金 5-30年 4.5%
//11年2月9日利率下限(7折)
lilv_array[13] = new Array;
lilv_array[13][1] = new Array;
lilv_array[13][2] = new Array;
lilv_array[13][1][5] = 0.04515;//商贷 1～5年 4.515%
lilv_array[13][1][10] = 0.04620;//商贷 5-30年 4.62%
lilv_array[13][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[13][2][10] = 0.0450;//公积金 5-30年 4.5%
//11年2月9日利率下限(85折)
lilv_array[14] = new Array;
lilv_array[14][1] = new Array;
lilv_array[14][2] = new Array;
lilv_array[14][1][5] = 0.054825;//商贷 1～5年 5.4825%
lilv_array[14][1][10] = 0.0561;//商贷 5-30年 5.61%
lilv_array[14][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[14][2][10] = 0.0450;//公积金 5-30年 4.5%
//11年2月9日利率上限(1.1倍)
lilv_array[15] = new Array;
lilv_array[15][1] = new Array;
lilv_array[15][2] = new Array;
lilv_array[15][1][5] = 0.07095;//商贷 1～5年 7.095%
lilv_array[15][1][10] = 0.0726;//商贷 5-30年 7.26%
lilv_array[15][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[15][2][10] = 0.0450;//公积金 5-30年 4.5%
//11年4月5日基准利率
lilv_array[16] = new Array;
lilv_array[16][1] = new Array;
lilv_array[16][2] = new Array;
lilv_array[16][1][5] = 0.0665;//商贷 1～5年 6.65%
lilv_array[16][1][10] = 0.0680;//商贷 5-30年 6.8%
lilv_array[16][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[16][2][10] = 0.0470;//公积金 5-30年 4.7%
//11年4月5日利率下限（7折）
lilv_array[17] = new Array;
lilv_array[17][1] = new Array;
lilv_array[17][2] = new Array;
lilv_array[17][1][5] = 0.04655;//商贷 1～5年 4.655%
lilv_array[17][1][10] = 0.0476;//商贷 5-30年 4.76%
lilv_array[17][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[17][2][10] = 0.0470;//公积金 5-30年 4.7%
//11年4月5日利率下限（85折）
lilv_array[18] = new Array;
lilv_array[18][1] = new Array;
lilv_array[18][2] = new Array;
lilv_array[18][1][5] = 0.056525;//商贷 1～5年 5.6525%
lilv_array[18][1][10] = 0.0578;//商贷 5-30年 5.78%
lilv_array[18][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[18][2][10] = 0.0470;//公积金 5-30年 4.7%
//11年4月5日利率上限（1.1倍）
lilv_array[19] = new Array;
lilv_array[19][1] = new Array;
lilv_array[19][2] = new Array;
lilv_array[19][1][5] = 0.07315;//商贷 1～5年 7.315%
lilv_array[19][1][10] = 0.0748;//商贷 5-30年 7.48%
lilv_array[19][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[19][2][10] = 0.0470;//公积金 5-30年 4.7%
//11年7月6日基准利率
lilv_array[20] = new Array;
lilv_array[20][1] = new Array;
lilv_array[20][2] = new Array;
lilv_array[20][1][5] = 0.0690;//商贷 1～5年 6.9%
lilv_array[20][1][10] = 0.0705;//商贷 5-30年 7.05%
lilv_array[20][2][5] = 0.0445;//公积金 1～5年 4.45%
lilv_array[20][2][10] = 0.0490;//公积金 5-30年 4.9%
//11年7月6日利率下限（7折）
lilv_array[21] = new Array;
lilv_array[21][1] = new Array;
lilv_array[21][2] = new Array;
lilv_array[21][1][5] = 0.0483;//商贷 1～5年 4.83%
lilv_array[21][1][10] = 0.04935;//商贷 5-30年 4.935%
lilv_array[21][2][5] = 0.0445;//公积金 1～5年 4.45%
lilv_array[21][2][10] = 0.0490;//公积金 5-30年 4.9%
//11年7月6日利率下限（85折）
lilv_array[22] = new Array;
lilv_array[22][1] = new Array;
lilv_array[22][2] = new Array;
lilv_array[22][1][5] = 0.05865;//商贷 1～5年 5.865%
lilv_array[22][1][10] = 0.059925;//商贷 5-30年 5.9925%
lilv_array[22][2][5] = 0.0445;//公积金 1～5年 4.45%
lilv_array[22][2][10] = 0.0490;//公积金 5-30年 4.9%
//11年7月6日利率上限（1.1倍）
lilv_array[23] = new Array;
lilv_array[23][1] = new Array;
lilv_array[23][2] = new Array;
lilv_array[23][1][5] = 0.0759;//商贷 1～5年 7.59%
lilv_array[23][1][10] = 0.07755;//商贷 5-30年 7.755%
lilv_array[23][2][5] = 0.0445;//公积金 1～5年 4.45%
lilv_array[23][2][10] = 0.0490;//公积金 5-30年 4.9%


//12年6月8日基准利率
lilv_array[24] = new Array;
lilv_array[24][1] = new Array;
lilv_array[24][2] = new Array;
lilv_array[24][1][5] = 0.0665;//商贷 1～5年 6.65%
lilv_array[24][1][10] = 0.0680;//商贷 5-30年 6.8%
lilv_array[24][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[24][2][10] = 0.0470;//公积金 5-30年 4.7%
//12年6月8日利率下限（7折）
lilv_array[25] = new Array;
lilv_array[25][1] = new Array;
lilv_array[25][2] = new Array;
lilv_array[25][1][5] = 0.04655;//商贷 1～5年 4.655%
lilv_array[25][1][10] = 0.0476;//商贷 5-30年 4.76%
lilv_array[25][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[25][2][10] = 0.0470;//公积金 5-30年 4.7%
//12年6月8日利率下限（85折）
lilv_array[26] = new Array;
lilv_array[26][1] = new Array;
lilv_array[26][2] = new Array;
lilv_array[26][1][5] = 0.056525;//商贷 1～5年 5.6525%
lilv_array[26][1][10] = 0.0578;//商贷 5-30年 5.78%
lilv_array[26][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[26][2][10] = 0.0470;//公积金 5-30年 4.7%
//12年6月8日利率上限（1.1倍）
lilv_array[27] = new Array;
lilv_array[27][1] = new Array;
lilv_array[27][2] = new Array;
lilv_array[27][1][5] = 0.07315;//商贷 1～5年 7.315%
lilv_array[27][1][10] = 0.0748;//商贷 5-30年 7.48%
lilv_array[27][2][5] = 0.0420;//公积金 1～5年 4.2%
lilv_array[27][2][10] = 0.0470;//公积金 5-30年 4.7%


//12年7月6日基准利率
lilv_array[28] = new Array;
lilv_array[28][1] = new Array;
lilv_array[28][2] = new Array;
lilv_array[28][1][5] = 0.0640;//商贷 1～5年 6.4%
lilv_array[28][1][10] = 0.0655;//商贷 5-30年 6.55%
lilv_array[28][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[28][2][10] = 0.0450;//公积金 5-30年 4.5%
//12年7月6日利率下限（7折）
lilv_array[29] = new Array;
lilv_array[29][1] = new Array;
lilv_array[29][2] = new Array;
lilv_array[29][1][5] = 0.0448;//商贷 1～5年 4.48%
lilv_array[29][1][10] = 0.04585;//商贷 5-30年 4.585%
lilv_array[29][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[29][2][10] = 0.0450;//公积金 5-30年 4.5%
//12年7月6日利率下限（85折）
lilv_array[30] = new Array;
lilv_array[30][1] = new Array;
lilv_array[30][2] = new Array;
lilv_array[30][1][5] = 0.0544;//商贷 1～5年 5.44%
lilv_array[30][1][10] = 0.055675;//商贷 5-30年 5.5675%
lilv_array[30][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[30][2][10] = 0.0450;//公积金 5-30年 4.5%
//12年7月6日利率上限（1.1倍）
lilv_array[31] = new Array;
lilv_array[31][1] = new Array;
lilv_array[31][2] = new Array;
lilv_array[31][1][5] = 0.0704;//商贷 1～5年 7.04%
lilv_array[31][1][10] = 0.07205;//商贷 5-30年 7.205%
lilv_array[31][2][5] = 0.0400;//公积金 1～5年 4%
lilv_array[31][2][10] = 0.0450;//公积金 5-30年 4.5%

      //20141124新增贷款利率
      //14年11月22日基准利率
      lilv_array[32] = new Array;
      lilv_array[32][1] = new Array;
      lilv_array[32][2] = new Array;
      lilv_array[32][1][1] = 0.0560;//商贷1年 6%
      lilv_array[32][1][3] = 0.0600;//商贷1～3年 6%
      lilv_array[32][1][5] = 0.0600;//商贷 3～5年 6%
      lilv_array[32][1][10] = 0.0615;//商贷 5-30年 6.15%
      lilv_array[32][2][5] = 0.0375;//公积金 1～5年 4%
      lilv_array[32][2][10] = 0.0425;//公积金 5-30年 4.5%
      //14年11月22日利率下限（7折）
      lilv_array[33] = new Array;
      lilv_array[33][1] = new Array;
      lilv_array[33][2] = new Array;
      lilv_array[33][1][1] = 0.0420;//商贷1年 6%
      lilv_array[33][1][3] = 0.0420;//商贷1～3年 6%
      lilv_array[33][1][5] = 0.0420;//商贷 3～5年 6%
      lilv_array[33][1][10] = 0.04305;//商贷 5-30年 6.15%
      lilv_array[33][2][5] = 0.02625;//公积金 1～5年 4%
      lilv_array[33][2][10] = 0.02975;//公积金 5-30年 4.5%
      //14年11月22日利率下限（85折）
      lilv_array[34] = new Array;
      lilv_array[34][1] = new Array;
      lilv_array[34][2] = new Array;
      lilv_array[34][1][1] = 0.051; //商贷1年 5.1%
      lilv_array[34][1][3] = 0.051; //商贷1～3年 5.2275%
      lilv_array[34][1][5] = 0.051; //商贷 3～5年 5.44%
      lilv_array[34][1][10] = 0.052275; //商贷 5-30年 5.5675%
      lilv_array[34][2][5] = 0.031875; //公积金 1～5年 4%
      lilv_array[34][2][10] = 0.036125; //公积金 5-30年 4.5%
      //14年11月22日利率上限（1.1倍）
      lilv_array[35] = new Array;
      lilv_array[35][1] = new Array;
      lilv_array[35][2] = new Array;
      lilv_array[35][1][1] = 0.066; //商贷1年 6.6%
      lilv_array[35][1][3] = 0.066; //商贷1～3年 6.765%
      lilv_array[35][1][5] = 0.066; //商贷 3～5年 7.04%
      lilv_array[35][1][10] = 0.06765; //商贷 5-30年 7.205%
      lilv_array[35][2][5] = 0.04125; //公积金 1～5年 4%
      lilv_array[35][2][10] = 0.04675; //公积金 5-30年 4.5%

      //2015年2月28日杨普法新增贷款利率
      //2015年3月1日基准利率
      lilv_array[36] = new Array;
      lilv_array[36][1] = new Array;
      lilv_array[36][2] = new Array;
      lilv_array[36][1][1] = 0.0535;//商贷1年 6%
      lilv_array[36][1][3] = 0.0575;//商贷1～3年 6%
      lilv_array[36][1][5] = 0.0575;//商贷 3～5年 6%
      lilv_array[36][1][10] = 0.0590;//商贷 5-30年 6.15%
      lilv_array[36][2][5] = 0.0350;//公积金 1～5年 4%
      lilv_array[36][2][10] = 0.0400;//公积金 5-30年 4.5%
      ///2015年3月1日利率下限（7折）
      lilv_array[37] = new Array;
      lilv_array[37][1] = new Array;
      lilv_array[37][2] = new Array;
      lilv_array[37][1][1] = 0.03745;//商贷1年 6%
      lilv_array[37][1][3] = 0.04025;//商贷1～3年 6%
      lilv_array[37][1][5] = 0.04025;//商贷 3～5年 6%
      lilv_array[37][1][10] = 0.04130;//商贷 5-30年 6.15%
      lilv_array[37][2][5] = 0.02450;//公积金 1～5年 4%
      lilv_array[37][2][10] = 0.02800;//公积金 5-30年 4.5%
      ///2015年3月1日利率下限（85折）
      lilv_array[38] = new Array;
      lilv_array[38][1] = new Array;
      lilv_array[38][2] = new Array;
      lilv_array[38][1][1] = 0.045475; //商贷1年 5.1%
      lilv_array[38][1][3] = 0.0488750; //商贷1～3年 5.2275%
      lilv_array[38][1][5] = 0.0488750; //商贷 3～5年 5.44%
      lilv_array[38][1][10] = 0.05015; //商贷 5-30年 5.5675%
      lilv_array[38][2][5] = 0.02975; //公积金 1～5年 4%
      lilv_array[38][2][10] = 0.03400; //公积金 5-30年 4.5%
      ///2015年3月1日利率上限（1.1倍）
      lilv_array[39] = new Array;
      lilv_array[39][1] = new Array;
      lilv_array[39][2] = new Array;
      lilv_array[39][1][1] = 0.05885; //商贷1年 6.6%
      lilv_array[39][1][3] = 0.06325; //商贷1～3年 6.765%
      lilv_array[39][1][5] = 0.06325; //商贷 3～5年 7.04%
      lilv_array[39][1][10] = 0.0649; //商贷 5-30年 7.205%
      lilv_array[39][2][5] = 0.03850; //公积金 1～5年 4%
      lilv_array[39][2][10] = 0.0440; //公积金 5-30年 4.5%


        //2015年5月11日张延科新增贷款利率!
        //2015年5月11日基准利率
        lilv_array[40] = new Array;
        lilv_array[40][1] = new Array;
        lilv_array[40][2] = new Array;
        lilv_array[40][1][1] = 0.0510;//商贷1年 6%
        lilv_array[40][1][3] = 0.0550;//商贷1～3年 6%
        lilv_array[40][1][5] = 0.0550;//商贷 3～5年 6%
        lilv_array[40][1][10] = 0.0565;//商贷 5-30年 6.15%
        lilv_array[40][2][5] = 0.03250;//公积金 1～5年 4%
        lilv_array[40][2][10] = 0.03750;//公积金 5-30年 4.5%
        //2015年5月11日利率下限（7折）
        lilv_array[41] = new Array;
        lilv_array[41][1] = new Array;
        lilv_array[41][2] = new Array;
        lilv_array[41][1][1] = 0.0357;//商贷1年 6%
        lilv_array[41][1][3] = 0.0385;//商贷1～3年 6%
        lilv_array[41][1][5] = 0.0385;//商贷 3～5年 6%
        lilv_array[41][1][10] = 0.03955;//商贷 5-30年 6.15%
        lilv_array[41][2][5] = 0.02275;//公积金 1～5年 4%
        lilv_array[41][2][10] = 0.02625;//公积金 5-30年 4.5%
        //2015年5月11日利率下限（85折）
        lilv_array[42] = new Array;
        lilv_array[42][1] = new Array;
        lilv_array[42][2] = new Array;
        lilv_array[42][1][1] = 0.04335; //商贷1年 5.1%
        lilv_array[42][1][3] = 0.04675; //商贷1～3年 5.2275%
        lilv_array[42][1][5] = 0.04675; //商贷 3～5年 5.44%
        lilv_array[42][1][10] = 0.048025; //商贷 5-30年 5.5675%
        lilv_array[42][2][5] = 0.027625; //公积金 1～5年 4%
        lilv_array[42][2][10] = 0.031875; //公积金 5-30年 4.5%
        //2015年5月11日利率上限（1.1倍）
        lilv_array[43] = new Array;
        lilv_array[43][1] = new Array;
        lilv_array[43][2] = new Array;
        lilv_array[43][1][1] = 0.0561; //商贷1年 6.6%
        lilv_array[43][1][3] = 0.0605; //商贷1～3年 6.765%
        lilv_array[43][1][5] = 0.0605; //商贷 3～5年 7.04%
        lilv_array[43][1][10] = 0.06215; //商贷 5-30年 7.435%
        lilv_array[43][2][5] = 0.03575; //公积金 1～5年 4%
        lilv_array[43][2][10] = 0.04125; //公积金 5-30年 4.5%


        //15年6月28日基准利率
        lilv_array[44] = new Array;
        lilv_array[44][1] = new Array;
        lilv_array[44][2] = new Array;
        lilv_array[44][1][1] = 0.0485;
        lilv_array[44][1][3] = 0.0525;
        lilv_array[44][1][5] = 0.0525;
        lilv_array[44][1][10] = 0.054;
        lilv_array[44][2][5] = 0.03;
        lilv_array[44][2][10] = 0.035;
        //15年6月28日利率下限（7折）
        lilv_array[45] = new Array;
        lilv_array[45][1] = new Array;
        lilv_array[45][2] = new Array;
        lilv_array[45][1][1] = 0.03395;
        lilv_array[45][1][3] = 0.03675;
        lilv_array[45][1][5] = 0.03675;
        lilv_array[45][1][10] = 0.0378;
        lilv_array[45][2][5] = 0.021;
        lilv_array[45][2][10] = 0.0245;
        //15年6月28日利率下限（85折）
        lilv_array[46] = new Array;
        lilv_array[46][1] = new Array;
        lilv_array[46][2] = new Array;
        lilv_array[46][1][1] = 0.041225;
        lilv_array[46][1][3] = 0.044625;
        lilv_array[46][1][5] = 0.044625;
        lilv_array[46][1][10] = 0.0459;
        lilv_array[46][2][5] = 0.0255;
        lilv_array[46][2][10] = 0.02975;
        //15年6月28日利率上限（1.1倍）
        lilv_array[47] = new Array;
        lilv_array[47][1] = new Array;
        lilv_array[47][2] = new Array;
        lilv_array[47][1][1] = 0.05335;
        lilv_array[47][1][3] = 0.05775;
        lilv_array[47][1][5] = 0.05775;
        lilv_array[47][1][10] = 0.0594;
        lilv_array[47][2][5] = 0.033;
        lilv_array[47][2][10] = 0.0385;
        //15年8月26日基准利率
        lilv_array[48] = new Array;
        lilv_array[48][1] = new Array;
        lilv_array[48][2] = new Array;
        lilv_array[48][1][1] = 0.046;
        lilv_array[48][1][3] = 0.05;
        lilv_array[48][1][5] = 0.05;
        lilv_array[48][1][10] = 0.0515;
        lilv_array[48][2][5] = 0.0275;
        lilv_array[48][2][10] = 0.0325;
        //15年8月26日利率下限（7折）
        lilv_array[49] = new Array;
        lilv_array[49][1] = new Array;
        lilv_array[49][2] = new Array;
        lilv_array[49][1][1] = 0.0322;
        lilv_array[49][1][3] = 0.035;
        lilv_array[49][1][5] = 0.035;
        lilv_array[49][1][10] = 0.03605;
        lilv_array[49][2][5] = 0.01925;
        lilv_array[49][2][10] = 0.02275;
        //15年8月26日利率下限（85折）
        lilv_array[50] = new Array;
        lilv_array[50][1] = new Array;
        lilv_array[50][2] = new Array;
        lilv_array[50][1][1] = 0.0391;
        lilv_array[50][1][3] = 0.0425;
        lilv_array[50][1][5] = 0.0425;
        lilv_array[50][1][10] = 0.043775;
        lilv_array[50][2][5] = 0.023375;
        lilv_array[50][2][10] = 0.027625;
        //15年8月26日利率上限（1.1倍）
        lilv_array[51] = new Array;
        lilv_array[51][1] = new Array;
        lilv_array[51][2] = new Array;
        lilv_array[51][1][1] = 0.0506;
        lilv_array[51][1][3] = 0.055;
        lilv_array[51][1][5] = 0.055;
        lilv_array[51][1][10] = 0.05665;
        lilv_array[51][2][5] = 0.03025;
        lilv_array[51][2][10] = 0.03575;

  
//15年10月24日基准利率
lilv_array[52] = new Array;
lilv_array[52][1] = new Array;
lilv_array[52][2] = new Array;
lilv_array[52][1][1] = 0.0435;
lilv_array[52][1][3] = 0.0475;
lilv_array[52][1][5] = 0.0475;
lilv_array[52][1][10] = 0.049;
lilv_array[52][2][5] = 0.0275;
lilv_array[52][2][10] = 0.0325;
//15年10月24日利率下限（7折）
lilv_array[53] = new Array;
lilv_array[53][1] = new Array;
lilv_array[53][2] = new Array;
lilv_array[53][1][1] = 0.03045;
lilv_array[53][1][3] = 0.03325;
lilv_array[53][1][5] = 0.03325;
lilv_array[53][1][10] = 0.0343;
lilv_array[53][2][5] = 0.01925;
lilv_array[53][2][10] = 0.02275;
//15年10月24日利率下限（85折）
lilv_array[54] = new Array;
lilv_array[54][1] = new Array;
lilv_array[54][2] = new Array;
lilv_array[54][1][1] = 0.036975;
lilv_array[54][1][3] = 0.040375;
lilv_array[54][1][5] = 0.040375;
lilv_array[54][1][10] = 0.04165;
lilv_array[54][2][5] = 0.023375;
lilv_array[54][2][10] = 0.027625;
//15年10月24日利率上限（1.1倍）
lilv_array[55] = new Array;
lilv_array[55][1] = new Array;
lilv_array[55][2] = new Array;
lilv_array[55][1][1] = 0.04785;
lilv_array[55][1][3] = 0.05225;
lilv_array[55][1][5] = 0.05225;
lilv_array[55][1][10] = 0.0539;
lilv_array[55][2][5] = 0.03025;
lilv_array[55][2][10] = 0.03575;

// 当前利率
var nowDkll = 0.049;
// 计算器
//本息还款的月还款额(参数: 年利率/贷款总额/贷款总月份)
function getMonthMoney1111(lilv, total, month) {
  var lilv_month = lilv / 12; //月利率
  return total * lilv_month * Math.pow(1 + lilv_month, month) / (Math.pow(1 + lilv_month, month) - 1);
}
/*
data-zj 模板上总价标签
data-sf 首付标签
data-yg 月供标签
data-ll 利率标签
 */
var zj = $('[data-zj]').text() * 1;
$('[data-ll]').html(nowDkll * 100).val(nowDkll * 100);
$('[data-sf]').html(Math.round(zj * .3 * 100) / 100);
$('[data-yg]').html(Math.round(getMonthMoney1111(nowDkll, zj * .7, 240) * 1000000) / 100);
var llData = {
  title: ['15年10月24日利率上限（1.1倍）',
    '15年10月24日利率下限（85折）',
    '15年10月24日利率下限（7折）',
    '15年10月24日基准利率',

    '15年8月26日利率上限（1.1倍）',
    '15年8月26日利率下限（85折）',
    '15年8月26日利率下限（7折）',
    '15年8月26日基准利率',

    '15年6月28日利率上限（1.1倍）',
    '15年6月28日利率下限（85折）',
    '15年6月28日利率下限（7折）',
    '15年6月28日基准利率',

    '15年5月11日利率上限（1.1倍）',
    '15年5月11日利率下限（85折）',
    '15年5月11日利率下限（7折）',
    '15年5月11日基准利率',

    '12年7月6日利率上限（1.1倍）',
    '12年7月6日利率下限（85折）',
    '12年7月6日利率下限（7折）',
    '12年7月6日基准利率',

    '12年6月8日利率上限（1.1倍）',
    '12年6月8日利率下限（85折）',
    '12年6月8日利率下限（7折）',
    '12年6月8日基准利率'
  ], 
  value: [55,
    54,
    53,
    52,

    51,
    50,
    49,
    48,

    47,
    46,
    45,
    44,

    43,
    42,
    41,
    40,

    31,
    30,
    29,
    28,

    27,
    26,
    25,
    24
  ]
}
$('[data-listdata]').each(function(i, el) {
  $(this).html(template($(this).data('listdata').replace(/#|./, ''), llData));
});