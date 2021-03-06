<?php
/**
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA;
 *
 */

namespace oat\tao\model\export;


use oat\oatbox\extension\AbstractAction;
use tao_helpers_Uri;
use core_kernel_classes_Class;
use tao_helpers_Export;
use common_exception_UserReadableException;
use common_report_Report;

class ExportTask extends AbstractAction
{
    function __invoke($params)
    {
        if(!isset($params['exportHandler'])) {
            throw new \common_Exception('Wrong option parameter, exporter missing');
        }

        if(!isset($params['userUri'])) {
            throw new \common_Exception('Wrong option parameter, user uri missing');
        }

        $label = (isset($params['taskLabel'])) ? $params['taskLabel'] : __('Export File');

        $exporterClassName =  $params['exportHandler'] ;
        $exporter = new $exporterClassName();

        if (isset($params['instances'])) {
            $instances = json_decode(urldecode($params['instances']));
            unset($params['instances']);

            foreach ($instances as $instance){
                $params['instances'][tao_helpers_Uri::decode($instance)] = tao_helpers_Uri::decode($instance);
            }
        } elseif (isset($params['exportInstance'])) {
            $params['exportInstance'] = tao_helpers_Uri::decode($params['exportInstance']);
        }


        //allow to export complete classes
        if(isset($params['classes'])){
            $classes = json_decode(urldecode($params['classes']));
            unset($params['classes']);


            $children = array();
            foreach ($classes as $classUri){
                $class = new core_kernel_classes_Class(tao_helpers_Uri::decode($classUri));
                $uris = array_keys($class->getInstances());
                $children = array_combine($uris,$uris);
            }

            if(empty($params['instances'])){
                $params['instances'] = [];
            }
            $params['instances'] = array_merge($params['instances'],$children);
        }

        $file = null;

        try {
            $report = $exporter->export($params, tao_helpers_Export::getExportPath());
        } catch (common_exception_UserReadableException $e) {
            $report = common_report_Report::createFailure($e->getUserMessage());
        }


        if ($report instanceof common_report_Report) {
            $file = $report->getData();


            if (is_null($file) || $report->getType() === common_report_Report::TYPE_ERROR || $report->containsError()) {
                $report->setType(common_report_Report::TYPE_ERROR);
                if (! $report->getMessage()) {
                    $report->setMessage(__('Error(s) has occurred during export.'));
                }

            } else {

                $fs = \tao_models_classes_TaoService::singleton()->getExportFileSource();

                \tao_helpers_File::copy($file, $fs->getPath().basename($file));
                $report->setData(basename($file));
            }
        } else {
            $report = common_report_Report::createFailure(__('Something went wrong during export'));
        }


        return $report;

    }


}