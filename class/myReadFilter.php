<?php
    /**
     * Created by PhpStorm.
     * User: Scott
     * Date: 6/18/2018
     * Time: 6:41 AM
     */

    /**  Define a Read Filter class implementing \PhpOffice\PhpSpreadsheet\Reader\IReadFilter  */
class MyReadFilter implements \PhpOffice\PhpSpreadsheet\Reader\IReadFilter
{
	public function readCell($column, $row, $worksheetName = '') {
		//  Read rows 1 to 7 and columns A to E only
		if ($row >= 8) {
			if (in_array($column,range('A','K'))) {
				return true;
			}
		}
		return false;
	}
}

