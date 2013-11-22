<?php defined('SYSPATH') or die('No direct script access.');


class Kmlfilter_Install {

	public function __construct() {
		$this->db = Database::instance();
	}

	public function run_install() {
		$this->db->query('DROP FUNCTION IF EXISTS `myWithin`;');
		$this->db->query('
			CREATE FUNCTION myWithin(p POINT, poly POLYGON) RETURNS INT(1) DETERMINISTIC
			BEGIN
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
			END;
			');
	}
	
	/**
	 * Function: uninstall
	 *
	 */
	public function uninstall() {
		$this->db->query('DROP FUNCTION IF EXISTS `myWithin`;');
	
	}
}