<?php

error_reporting(E_ALL);

/**
 * Generis Object Oriented API - tao/helpers/form/elements/class.Combobox.php
 *
 * $Id$
 *
 * This file is part of Generis Object Oriented API.
 *
 * Automatically generated on 06.07.2010, 17:45:45 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include tao_helpers_form_elements_MultipleElement
 *
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 */
require_once('tao/helpers/form/elements/class.MultipleElement.php');

/* user defined includes */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001985-includes begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001985-includes end

/* user defined constants */
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001985-constants begin
// section 127-0-1-1-3ed01c83:12409dc285c:-8000:0000000000001985-constants end

/**
 * Short description of class tao_helpers_form_elements_Combobox
 *
 * @abstract
 * @access public
 * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
 * @package tao
 * @subpackage helpers_form_elements
 */
abstract class tao_helpers_form_elements_Combobox
    extends tao_helpers_form_elements_MultipleElement
{
    // --- ASSOCIATIONS ---


    // --- ATTRIBUTES ---

    /**
     * Short description of attribute widget
     *
     * @access protected
     * @var string
     */
    protected $widget = 'http://www.tao.lu/datatypes/WidgetDefinitions.rdf#ComboBox';

    /**
     * Short description of attribute emptyOption
     *
     * @access protected
     * @var string
     */
    protected $emptyOption = '';

    // --- OPERATIONS ---

    /**
     * Short description of method setEmptyOption
     *
     * @access public
     * @author Bertrand Chevrier, <bertrand.chevrier@tudor.lu>
     * @param  string display
     * @return mixed
     */
    public function setEmptyOption($display)
    {
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B47 begin
		
		$this->emptyOption = $display;
		
        // section 127-0-1-1-47336e64:124c90d0af6:-8000:0000000000001B47 end
    }

} /* end of abstract class tao_helpers_form_elements_Combobox */

?>