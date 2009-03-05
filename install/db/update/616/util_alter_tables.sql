/**
 * MySQL file.
 *
 * PHP version 5
 *
 * LICENSE: This file is part of Yet Another Php Eve Api library also know
 * as Yapeal which will be used to refer to it in the rest of this license.
 *
 *  Yapeal is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  Yapeal is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with Yapeal. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Claus Pedersen <satissis@gmail.com>
 * @author Michael Cummings <mgcummings@yahoo.com>
 * @copyright Copyright (c) 2008-2009, Claus Pedersen, Michael Cummings
 * @license http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @package Yapeal
 */
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- Data `utilconfig`
-- -----------------------------------------------------
INSERT INTO `%prefix%utilconfig` (`Name`,`Value`) VALUES
('charAPIs', '%activeCharAPI%'),
('corpAPIs', '%activeCorpAPI%')
ON DUPLICATE KEY UPDATE `Value`=VALUES(`Value`);

-- -----------------------------------------------------
-- Alter `utilRegisteredCharacter`
-- -----------------------------------------------------
ALTER TABLE `%prefix%utilRegisteredCharacter`
  ADD COLUMN `activeAPI` TEXT COMMENT 'A space separated list of APIs to get for this character' FIRST;

-- -----------------------------------------------------
-- Update `utilRegisteredCharacter`
-- -----------------------------------------------------
UPDATE `%prefix%utilRegisteredCharacter`
  SET `activeAPI` = '%activeCharAPI%';

-- -----------------------------------------------------
-- Alter `utilRegisteredCorporation`
-- -----------------------------------------------------
ALTER TABLE `%prefix%utilRegisteredCorporation`
  ADD COLUMN `activeAPI` TEXT COMMENT 'A space separated list of APIs to get for this corporation' FIRST;

-- -----------------------------------------------------
-- Update `utilRegisteredCorporation`
-- -----------------------------------------------------
UPDATE `%prefix%utilRegisteredCorporation`
  SET `activeAPI` = '%activeCorpAPI%';

-- -----------------------------------------------------
SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
