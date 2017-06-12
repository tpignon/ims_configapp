TRUNCATE TABLE pharmareport_config.import_geo_sales_rep;

-- CSV file must be in C:\ProgramData\MySQL\MySQL Server 5.7\Data\pharmareport_config
LOAD DATA INFILE 'PharmaReport_Geo_SalesRep_Mapping.csv' 
INTO TABLE pharmareport_config.import_geo_sales_rep 
CHARACTER SET CP1250
FIELDS TERMINATED BY ';'  
OPTIONALLY ENCLOSED BY '"'
LINES TERMINATED BY '\n'
IGNORE 1 rows
(@col1,@col2,@col3,@col4,@col5,@col6,@col7,@col8,@col9,@col10)
SET client_output_id=@col1,version_geo_structure_code=@col2,geo_team=@col3,geo_level_number=@col4,geo_value=@col5,sr_first_name=@col6,sr_last_name=@col7,sr_email=@col8
;

-- Update only concerned DATASETS
DELETE FROM geo_sales_rep
WHERE geo_sales_rep.client_output_id IN
(SELECT DISTINCT client_output_id FROM import_geo_sales_rep);

INSERT INTO geo_sales_rep 
(`client_output_id`,
`version_geo_structure_code`,
`geo_team`,
`geo_level_number`,
`geo_value`,
`sr_first_name`,
`sr_last_name`,
`sr_email`)
SELECT * FROM import_geo_sales_rep;