<?php
/**
 * Contains CallList class.
 *
 * PHP version 5.3
 *
 * LICENSE:
 * This file is part of 1.1.x-WIP
 * Copyright (C) 2014 Michael Cummings
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU Lesser General
 * Public License as published by the Free Software Foundation, either version 3 of the License, or (at your option)
 * any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public License for more
 * details.
 *
 * You should have received a copy of the GNU Lesser General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 *
 * You should be able to find a copy of this license in the LICENSE.md file. A copy of the GNU GPL should also be
 * available in the GNU-GPL.md file.
 *
 * @copyright 2014 Michael Cummings
 * @license   http://www.gnu.org/copyleft/lesser.html GNU LGPL
 * @author    Michael Cummings <mgcummings@yahoo.com>
 * @author    Stephen Gulick <stephenmg12@gmail.com>
 */
namespace Yapeal\Database\Eve;

use PDOException;
use Yapeal\Database\AbstractCommonEveApi;
use Yapeal\Database\ApiNameTrait;
use Yapeal\Database\AttributesDatabasePreserver;
use Yapeal\Database\DatabasePreserverInterface;
use Yapeal\Database\SectionNameTrait;
use Yapeal\Xml\EveApiPreserverInterface;
use Yapeal\Xml\EveApiReadWriteInterface;
use Yapeal\Xml\EveApiRetrieverInterface;
use Yapeal\Xml\EveApiXmlModifyInterface;

/**
 * Class CallList
 */
class RefTypes extends AbstractCommonEveApi
{
    use ApiNameTrait, SectionNameTrait;
    /**
     * @param EveApiReadWriteInterface $data
     * @param EveApiRetrieverInterface $retrievers
     * @param EveApiPreserverInterface $preservers
     * @param int                      $interval
     */
    public function autoMagic(
        EveApiReadWriteInterface $data,
        EveApiRetrieverInterface $retrievers,
        EveApiPreserverInterface $preservers,
        $interval
    ) {
        $this->getLogger()
             ->info(
                 sprintf(
                     'Starting autoMagic for %1$s/%2$s',
                     $this->getSectionName(),
                     $this->getApiName()
                 )
             );
        /**
         * @var EveApiReadWriteInterface|EveApiXmlModifyInterface $data
         */
        $data->setEveApiSectionName(strtolower($this->getSectionName()))
             ->setEveApiName($this->getApiName())
             ->setEveApiXml();
        if ($this->cacheNotExpired(
            $this->getApiName(),
            $this->getSectionName()
        )
        ) {
            return;
        }
        if (!$this->gotApiLock($data)) {
            return;
        }
        $retrievers->retrieveEveApi($data);
        if ($data->getEveApiXml() === false) {
            $mess = sprintf(
                'Could NOT retrieve Eve Api data for %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->debug($mess);
            return;
        }
        $this->xsltTransform($data);
        if ($this->isInvalid($data)) {
            $mess = sprintf(
                'Data retrieved is invalid for %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess);
            $data->setEveApiName('Invalid' . $this->getApiName());
            $preservers->preserveEveApi($data);
            return;
        }
        $preservers->preserveEveApi($data);
        $preserver = new AttributesDatabasePreserver(
            $this->getPdo(),
            $this->getLogger(),
            $this->getCsq()
        );
        $this->preserveToRefTypes($preserver, $data->getEveApiXml());
        $this->updateCachedUntil($data, $interval, '0');
    }
    /**
     * @param DatabasePreserverInterface $preserver
     * @param string                     $xml
     */
    protected function preserveToRefTypes(
        DatabasePreserverInterface $preserver,
        $xml
    ) {
        $columnDefaults = array(
            'refTypeID' => null,
            'refTypeName' => null
        );
        $tableName = 'eveRefTypes';
        $sql = $this->getCsq()
                    ->getDeleteFromTable($tableName);
        $this->getLogger()
             ->info($sql);
        try {
            $this->getPdo()
                 ->beginTransaction();
            $this->getPdo()
                 ->exec($sql);
            $preserver->setTableName($tableName)
                      ->setColumnDefaults($columnDefaults)
                      ->preserveData($xml);
            $this->getPdo()
                 ->commit();
        } catch (PDOException $exc) {
            $mess = sprintf(
                'Failed to upsert data from Eve API %1$s/%2$s',
                strtolower($this->getSectionName()),
                $this->getApiName()
            );
            $this->getLogger()
                 ->warning($mess, array('exception' => $exc));
            $this->getPdo()
                 ->rollBack();
        }
    }
}
