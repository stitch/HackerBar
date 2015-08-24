-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema hackerbar
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema hackerbar
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `hackerbar` DEFAULT CHARACTER SET utf8 ;
USE `hackerbar` ;

-- -----------------------------------------------------
-- Table `hackerbar`.`account`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`account` (
  `name` VARCHAR(45) NOT NULL,
  `deposit` INT(11) NOT NULL COMMENT 'An amount in cents that is deposited by the accountholder. Normally this can be \"taken\" from the bar by the account holder at any time, similar to a bank deposit. - An individual accounts deposit may not be higher than 150 euro, otherwise the extensive rules of the banking sector will apply to this system.',
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`name`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`depositmutation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`depositmutation` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `account` VARCHAR(45) NOT NULL,
  `cents` INT(11) NOT NULL,
  `reason` TEXT NOT NULL COMMENT 'These are long stories written by the system, of all products bought and such. ',
  `datetime` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_deposit_account1_idx` (`account` ASC),
  CONSTRAINT `fk_deposit_account1`
    FOREIGN KEY (`account`)
    REFERENCES `hackerbar`.`account` (`name`)
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 185
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`log` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `datetime` DATETIME NOT NULL,
  `message` VARCHAR(255) NOT NULL,
  `explanation` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 3
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`transaction`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`transaction` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `price` INT(11) NOT NULL COMMENT 'The name \"price\" was chosen to indicate money, instead of a complication between the amount of sould products and the amount of money that was in the transaction. This can be complicated.',
  `price calculation` TEXT NOT NULL COMMENT 'How the price came to be. This will be usually left empty.\n',
  `datetime` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB
AUTO_INCREMENT = 133
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`payshare`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`payshare` (
  `transaction` INT(11) NOT NULL,
  `account` VARCHAR(45) NOT NULL,
  `amount` INT(11) NOT NULL,
  PRIMARY KEY (`transaction`, `account`),
  INDEX `fk_payshare_transaction1_idx` (`transaction` ASC),
  INDEX `fk_payshare_account1_idx` (`account` ASC),
  CONSTRAINT `fk_payshare_account1`
    FOREIGN KEY (`account`)
    REFERENCES `hackerbar`.`account` (`name`)
    ON UPDATE CASCADE,
  CONSTRAINT `fk_payshare_transaction1`
    FOREIGN KEY (`transaction`)
    REFERENCES `hackerbar`.`transaction` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`product group`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`product group` (
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`name`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`product`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`product` (
  `name` VARCHAR(45) NOT NULL,
  `price` VARCHAR(45) NOT NULL COMMENT 'in cents',
  `purchasable` TINYINT(1) NOT NULL DEFAULT '0',
  `description` VARCHAR(45) NOT NULL,
  `stock` INT(11) NOT NULL COMMENT 'stock should be the only field updatable. but that is something mysqlwb does not let you design.',
  `group` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`name`),
  INDEX `fk_product_product group1_idx` (`group` ASC),
  CONSTRAINT `fk_product_product group1`
    FOREIGN KEY (`group`)
    REFERENCES `hackerbar`.`product group` (`name`)
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`productcode`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`productcode` (
  `product` VARCHAR(45) NOT NULL,
  `code` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`code`, `product`),
  INDEX `fk_productcode_product1_idx` (`product` ASC),
  CONSTRAINT `fk_productcode_product1`
    FOREIGN KEY (`product`)
    REFERENCES `hackerbar`.`product` (`name`)
    ON UPDATE CASCADE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`sold product`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`sold product` (
  `transaction` INT(11) NOT NULL,
  `product` VARCHAR(45) NOT NULL,
  `amount` INT(11) NOT NULL COMMENT 'The amount for which the product was sold at the time of the transaction.\n',
  `price` INT(11) NOT NULL COMMENT 'In cents, this is the price per product sold, not the sum of the products. (?)\n',
  PRIMARY KEY (`transaction`, `product`),
  INDEX `fk_sold product_transaction_idx` (`transaction` ASC),
  INDEX `fk_sold product_product1_idx` (`product` ASC),
  CONSTRAINT `fk_sold product_product1`
    FOREIGN KEY (`product`)
    REFERENCES `hackerbar`.`product` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_sold product_transaction`
    FOREIGN KEY (`transaction`)
    REFERENCES `hackerbar`.`transaction` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`unlisted product`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`unlisted product` (
  `id` INT(11) NOT NULL AUTO_INCREMENT,
  `transaction` INT(11) NOT NULL,
  `possible name` VARCHAR(45) NOT NULL,
  `amount` INT(11) NOT NULL,
  `price` INT(11) NOT NULL,
  PRIMARY KEY (`id`, `transaction`),
  INDEX `fk_unlisted product_transaction1_idx` (`transaction` ASC),
  CONSTRAINT `fk_unlisted product_transaction1`
    FOREIGN KEY (`transaction`)
    REFERENCES `hackerbar`.`transaction` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8;


-- -----------------------------------------------------
-- Table `hackerbar`.`command log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`command log` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `command` VARCHAR(255) NOT NULL,
  `session` VARCHAR(32) NOT NULL,
  `ip` VARBINARY(16) NOT NULL,
  `datetime` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `hackerbar`.`session`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`session` (
  `id` VARCHAR(32) NOT NULL,
  `created` DATETIME NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;

USE `hackerbar` ;

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`deposit value`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`deposit value` (`SUM(account.deposit)` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`popular products`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`popular products` (`account` INT, `product` INT, `amountsold` INT, `payshare` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`recently sold products`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`recently sold products` (`account` INT, `transactionvalue` INT, `payshare` INT, `transaction` INT, `datetime` INT, `product` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`shopping list`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`shopping list` (`name` INT, `group` INT, `stock` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`stock value`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`stock value` (`SUM(product.stock * product.price)` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`transaction value per month`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`transaction value per month` (`month` INT, `year` INT, `revenue` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`transaction value per week`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`transaction value per week` (`month` INT, `week` INT, `year` INT, `revenue` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`transactionhistory`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`transactionhistory` (`id` INT, `price` INT, `datetime` INT, `nraccounts` INT, `accounts` INT, `spreadamounts` INT, `roundingerror` INT, `nrproducts` INT, `products` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`chart popularity`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`chart popularity` (`name` INT, `month` INT, `year` INT, `amount_sold` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`chart ranking`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`chart ranking` (`chart_product` INT, `chart_amount` INT, `chart_month` INT, `chart_year` INT, `chart_position` INT);

-- -----------------------------------------------------
-- Placeholder table for view `hackerbar`.`monthly chart`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `hackerbar`.`monthly chart` (`source_chart_product` INT, `source_chart_amount` INT, `source_chart_month` INT, `source_chart_year` INT, `source_chart_position` INT, `positions_altered` INT);

-- -----------------------------------------------------
-- View `hackerbar`.`deposit value`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`deposit value`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`deposit value` AS select sum(`hackerbar`.`account`.`deposit`) AS `SUM(account.deposit)` from `hackerbar`.`account`;

-- -----------------------------------------------------
-- View `hackerbar`.`popular products`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`popular products`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`popular products` AS select `hackerbar`.`account`.`name` AS `account`,`hackerbar`.`sold product`.`product` AS `product`,sum(`hackerbar`.`sold product`.`amount`) AS `amountsold`,sum(`hackerbar`.`payshare`.`amount`) AS `payshare` from (((`hackerbar`.`account` join `hackerbar`.`payshare` on((`hackerbar`.`account`.`name` = `hackerbar`.`payshare`.`account`))) join `hackerbar`.`transaction` on((`hackerbar`.`payshare`.`transaction` = `hackerbar`.`transaction`.`id`))) join `hackerbar`.`sold product` on((`hackerbar`.`transaction`.`id` = `hackerbar`.`sold product`.`transaction`))) group by `hackerbar`.`payshare`.`account`,`hackerbar`.`sold product`.`product` order by `amountsold` desc;

-- -----------------------------------------------------
-- View `hackerbar`.`recently sold products`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`recently sold products`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`recently sold products` AS select `hackerbar`.`account`.`name` AS `account`,`hackerbar`.`transaction`.`price` AS `transactionvalue`,`hackerbar`.`payshare`.`amount` AS `payshare`,`hackerbar`.`transaction`.`id` AS `transaction`,`hackerbar`.`transaction`.`datetime` AS `datetime`,`hackerbar`.`sold product`.`product` AS `product` from (((`hackerbar`.`account` join `hackerbar`.`payshare` on((`hackerbar`.`account`.`name` = `hackerbar`.`payshare`.`account`))) join `hackerbar`.`transaction` on((`hackerbar`.`payshare`.`transaction` = `hackerbar`.`transaction`.`id`))) join `hackerbar`.`sold product` on((`hackerbar`.`transaction`.`id` = `hackerbar`.`sold product`.`transaction`))) order by `hackerbar`.`transaction`.`datetime` desc;

-- -----------------------------------------------------
-- View `hackerbar`.`shopping list`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`shopping list`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`shopping list` AS select `hackerbar`.`product`.`name` AS `name`,`hackerbar`.`product`.`group` AS `group`,`hackerbar`.`product`.`stock` AS `stock` from `hackerbar`.`product` where (`hackerbar`.`product`.`stock` < 15);

-- -----------------------------------------------------
-- View `hackerbar`.`stock value`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`stock value`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`stock value` AS select sum((`hackerbar`.`product`.`stock` * `hackerbar`.`product`.`price`)) AS `SUM(product.stock * product.price)` from `hackerbar`.`product`;

-- -----------------------------------------------------
-- View `hackerbar`.`transaction value per month`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`transaction value per month`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`transaction value per month` AS select monthname(`hackerbar`.`transaction`.`datetime`) AS `month`,year(`hackerbar`.`transaction`.`datetime`) AS `year`,sum(`hackerbar`.`transaction`.`price`) AS `revenue` from `hackerbar`.`transaction` group by month(`hackerbar`.`transaction`.`datetime`) order by month(`hackerbar`.`transaction`.`datetime`);

-- -----------------------------------------------------
-- View `hackerbar`.`transaction value per week`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`transaction value per week`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`transaction value per week` AS select monthname(`hackerbar`.`transaction`.`datetime`) AS `month`,week(`hackerbar`.`transaction`.`datetime`,0) AS `week`,year(`hackerbar`.`transaction`.`datetime`) AS `year`,sum(`hackerbar`.`transaction`.`price`) AS `revenue` from `hackerbar`.`transaction` group by week(`hackerbar`.`transaction`.`datetime`,0) order by week(`hackerbar`.`transaction`.`datetime`,0);

-- -----------------------------------------------------
-- View `hackerbar`.`transactionhistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`transactionhistory`;
USE `hackerbar`;
CREATE  OR REPLACE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `hackerbar`.`transactionhistory` AS select `hackerbar`.`transaction`.`id` AS `id`,`hackerbar`.`transaction`.`price` AS `price`,`hackerbar`.`transaction`.`datetime` AS `datetime`,count(`hackerbar`.`payshare`.`account`) AS `nraccounts`,group_concat(`hackerbar`.`payshare`.`account` order by `hackerbar`.`payshare`.`amount` DESC separator ', ') AS `accounts`,group_concat(`hackerbar`.`payshare`.`amount` order by `hackerbar`.`payshare`.`amount` DESC separator ', ') AS `spreadamounts`,(sum(`hackerbar`.`payshare`.`amount`) - `hackerbar`.`transaction`.`price`) AS `roundingerror`,(select sum(`hackerbar`.`sold product`.`amount`) from `hackerbar`.`sold product` where (`hackerbar`.`sold product`.`transaction` = `hackerbar`.`transaction`.`id`)) AS `nrproducts`,(select group_concat(concat(`hackerbar`.`sold product`.`amount`,'x ',`hackerbar`.`sold product`.`product`) order by `hackerbar`.`sold product`.`amount` ASC separator ', ') from `hackerbar`.`sold product` where (`hackerbar`.`sold product`.`transaction` = `hackerbar`.`transaction`.`id`)) AS `products` from ((`hackerbar`.`transaction` join `hackerbar`.`payshare` on((`hackerbar`.`transaction`.`id` = `hackerbar`.`payshare`.`transaction`))) join `hackerbar`.`account` on((`hackerbar`.`payshare`.`account` = `hackerbar`.`account`.`name`))) group by `hackerbar`.`transaction`.`id` order by `hackerbar`.`transaction`.`id` desc;

-- -----------------------------------------------------
-- View `hackerbar`.`chart popularity`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`chart popularity`;
USE `hackerbar`;
CREATE  OR REPLACE VIEW `chart popularity` AS
SELECT hackerbar.product.name, MONTH(transaction.datetime) as month, YEAR(transaction.datetime) as year, count(hackerbar.`sold product`.amount) amount_sold FROM hackerbar.transaction 
INNER JOIN hackerbar.payshare
	 ON hackerbar.transaction.id = hackerbar.payshare.transaction 
	INNER JOIN hackerbar.`sold product`
	 ON hackerbar.transaction.id = hackerbar.`sold product`.transaction
	INNER JOIN hackerbar.product
	 ON hackerbar.`sold product`.product = hackerbar.product.name
GROUP BY (hackerbar.product.name), MONTH(transaction.datetime), YEAR(transaction.datetime);

-- -----------------------------------------------------
-- View `hackerbar`.`chart ranking`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`chart ranking`;
USE `hackerbar`;
CREATE  OR REPLACE VIEW `chart ranking` AS
SELECT
	hackerbar.product.name as chart_product,
	count(hackerbar.payshare.amount) as chart_amount,
	MONTH(transaction.datetime) as chart_month,
	YEAR(transaction.datetime) as chart_year,
    (select count(*) FROM `chart popularity` WHERE name != chart_product AND chart_month = month AND chart_year = year AND count(hackerbar.payshare.amount) < `chart popularity`.amount_sold) +1 as chart_position
FROM
	hackerbar.transaction
	INNER JOIN hackerbar.payshare
	 ON hackerbar.transaction.id = hackerbar.payshare.transaction
	INNER JOIN hackerbar.`sold product`
	 ON hackerbar.transaction.id = hackerbar.`sold product`.transaction
	INNER JOIN hackerbar.product
	 ON hackerbar.`sold product`.product = hackerbar.product.name
GROUP BY
	MONTH(transaction.datetime),
	hackerbar.product.name
ORDER BY chart_year DESC, chart_month DESC, chart_position ASC;

-- -----------------------------------------------------
-- View `hackerbar`.`monthly chart`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `hackerbar`.`monthly chart`;
USE `hackerbar`;
CREATE  OR REPLACE VIEW `monthly chart` AS 
SELECT 
chart_product as source_chart_product,
chart_amount as source_chart_amount,
chart_month as source_chart_month,
chart_year as source_chart_year,
chart_position as source_chart_position,
IFNULL((select chart_position - source_chart_position FROM `chart ranking` WHERE chart_product = source_chart_product AND chart_month = IF(chart_month = 1, 12, source_chart_month -1) AND chart_year = IF(chart_month = 1, source_chart_year - 1, source_chart_year)), "new") as positions_altered
FROM `chart ranking` cr 
;
CREATE USER 'baruser';

CREATE USER 'baradmin';


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
