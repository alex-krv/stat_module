CREATE TABLE IF NOT EXISTS `vogood_statistics` ( 
	`id` INT(18) NOT NULL AUTO_INCREMENT ,
	`store_id` VARCHAR(50) NOT NULL , 
	`date` DATE NOT NULL,
	`product_views_count` INT(18) , 
	`product_viewing_totaltime` FLOAT(18) ,
	`storeсard_visits_count` INT(18) , 
	`product_views_count_from_store` INT(18) , 
	`product_views_count_from_catalog` INT(18) , 
	`site_visits_count` INT(18) , 
	PRIMARY KEY (`id`)
) 

