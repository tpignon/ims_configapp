TRUNCATE TABLE pharmareport_config.dwh_d_ph_geo_sales_rep;
LOAD DATA INFILE 'C:/ProgramData/MySQL/MySQL Server 5.7/Data/pharmareport_config/d_ph_geo_sales_rep.csv' 
	INTO TABLE pharmareport_config.dwh_d_ph_geo_sales_rep 
	CHARACTER SET CP1250
	FIELDS 
		TERMINATED BY ';'  
		OPTIONALLY ENCLOSED BY '"'
	LINES TERMINATED BY '\n'
	IGNORE 1 rows
    (@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10,@col11,@col12,@col13,@col14,@col15,@col16)
	SET 
		ds_bk=@col1,
        mkt_level1=@col2,
        geo_level1=@col3,
        geo_level2=@col4,
        geo_level3=@col5,
        geo_level4=@col6,
        geo_level5=@col7,
        geo_level6=@col8,
        geo_level7=@col9,
        slsrep_level1_bk=@col10,
        slsrep_level2_bk=@col11,
        slsrep_level3_bk=@col12,
        slsrep_level4_bk=@col13,
        slsrep_level5_bk=@col14,
        slsrep_level6_bk=@col15,
        slsrep_level7_bk=@col16
;