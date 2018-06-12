-- phpMyAdmin SQL Dump
-- version 4.7.7
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Jun 12, 2018 at 03:13 AM
-- Server version: 5.6.39
-- PHP Version: 5.6.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

DELIMITER $$
--
-- Functions
--
DROP FUNCTION IF EXISTS `myWithin`$$
CREATE FUNCTION `myWithin` (`p` POINT, `poly` POLYGON) RETURNS INT(1) BEGIN
			DECLARE n INT DEFAULT 0;
			DECLARE pX DECIMAL(9,6);
			DECLARE pY DECIMAL(9,6);
			DECLARE ls LINESTRING;
			DECLARE poly1 POINT;
			DECLARE poly1X DECIMAL(9,6);
			DECLARE poly1Y DECIMAL(9,6);
			DECLARE poly2 POINT;
			DECLARE poly2X DECIMAL(9,6);
			DECLARE poly2Y DECIMAL(9,6);
			DECLARE i INT DEFAULT 0;
			DECLARE result INT(1) DEFAULT 0;
			SET pX = X(p);
			SET pY = Y(p);
			SET ls = ExteriorRing(poly);
			SET poly2 = EndPoint(ls);
			SET poly2X = X(poly2);
			SET poly2Y = Y(poly2);
			SET n = NumPoints(ls);
			WHILE i<n DO
			SET poly1 = PointN(ls, (i+1));
			SET poly1X = X(poly1);
			SET poly1Y = Y(poly1);
			IF ( ( ( ( poly1X <= pX ) && ( pX < poly2X ) ) || ( ( poly2X <= pX ) && ( pX < poly1X ) ) ) && ( pY > ( poly2Y - poly1Y ) * ( pX - poly1X ) / ( poly2X - poly1X ) + poly1Y ) ) THEN
			SET result = !result;
			END IF;
			SET poly2X = poly1X;
			SET poly2Y = poly1Y;
			SET i = i + 1;
			END WHILE;
			RETURN result;
			END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `vw_form_autosearch_option`
-- (See below for the actual view)
--
DROP VIEW IF EXISTS `vw_form_autosearch_option`;
CREATE TABLE IF NOT EXISTS `vw_form_autosearch_option` (
`form_field_id` varchar(45)
,`id` varchar(255)
,`text` mediumtext
,`parent` varchar(255)
,`disabled` int(11)
);

-- --------------------------------------------------------

--
-- Structure for view `vw_form_autosearch_option`
--
DROP TABLE IF EXISTS `vw_form_autosearch_option`;

CREATE VIEW `vw_form_autosearch_option`  AS  select `child`.`form_field_id` AS `form_field_id`,`child`.`value` AS `id`,concat_ws(' - ',`parent`.`text`,`child`.`text`) AS `text`,`child`.`parent` AS `parent`,`child`.`disabled` AS `disabled` from (`form_autosearch_option` `child` left join `form_autosearch_option` `parent` on(((`parent`.`value` = `child`.`parent`) and (`parent`.`form_field_id` = `child`.`form_field_id`)))) ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
