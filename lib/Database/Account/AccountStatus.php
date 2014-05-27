<?php
/**
 * Contains AccountStatus class.
 *
 * PHP version 5
 *
 * LICENSE:
 * This file is part of Yet Another Php Eve Api Library also know as Yapeal which can be used to access the Eve Online
 * API data and place it into a database.
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Lesser General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Lesser General Public License for more details.
 *
 *  You should have received a copy of the GNU Lesser General Public License
 *  along with this program. If not, see <http://www.gnu.org/licenses/>.
 *
 * @author     Michael Cummings <mgcummings@yahoo.com>
 * @copyright  Copyright (c) 2008-2014, Michael Cummings
 * @license    http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @link       http://code.google.com/p/yapeal/
 * @link       http://www.eveonline.com/
 */
namespace Yapeal\Database\Account;

use Psr\Log\LoggerInterface;
use Yapeal\Database\AbstractAccount;
use Yapeal\Database\QueryBuilder;

/**
 * Class used to fetch and store eve SkillTree API.
 */
class AccountStatus extends AbstractAccount
{
    /**
     * Constructor
     *
     * @param array           $params Holds the required parameters like keyID, vCode, etc
     *                                used in HTML POST parameters to API servers which varies depending on API
     *                                'section' being requested.
     * @param LoggerInterface $logger
     *
     * @throws \LengthException for any missing required $params.
     */
    public function __construct(array $params, LoggerInterface $logger)
    {
        $this->section = strtolower(basename(__DIR__));
        $this->api = basename(__CLASS__);
        parent::__construct($params, $logger);
    }
    /**
     * Per API parser for XML.
     *
     * @return bool Returns TRUE if XML was parsed correctly, FALSE if not.
     */
    protected function parserAPI()
    {
        $tableName = YAPEAL_TABLE_PREFIX . $this->section . $this->api;
        // Get a new query instance.
        $qb = new QueryBuilder($tableName, YAPEAL_DSN);
        // Set any column defaults needed.
        $qb->setDefault('keyID', $this->params['keyID']);
        $row = array();
        try {
            while ($this->reader->read()) {
                switch ($this->reader->nodeType) {
                    case \XMLReader::ELEMENT:
                        switch ($this->reader->localName) {
                            case 'createDate':
                            case 'logonCount':
                            case 'logonMinutes':
                            case 'paidUntil':
                                // Grab node name.
                                $name = $this->reader->localName;
                                // Move to text node.
                                $this->reader->read();
                                $row[$name] = $this->reader->value;
                                break;
                            default: // Nothing to do.
                        }
                        break;
                    case \XMLReader::END_ELEMENT:
                        if ($this->reader->localName == 'result') {
                            $qb->addRow($row);
                            $qb->store();
                            $qb = null;
                            return true;
                        }
                        break;
                    default: // Nothing to do.
                }
            }
        } catch (\ADODB_Exception $e) {
            $this->logger->error($e);
            \Logger::getLogger('yapeal')
                   ->error($e);
            return false;
        }
        $mess =
            'Function ' . __FUNCTION__ . ' did not exit correctly' . PHP_EOL;
        $this->logger->warning($mess);
        \Logger::getLogger('yapeal')
               ->warn($mess);
        return false;
    }
}
