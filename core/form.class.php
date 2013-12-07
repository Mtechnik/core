<?php
 /*
 * Project:		EQdkp-Plus
 * License:		Creative Commons - Attribution-Noncommercial-Share Alike 3.0 Unported
 * Link:		http://creativecommons.org/licenses/by-nc-sa/3.0/
 * -----------------------------------------------------------------------
 * Began:		2013
 * Date:		$Date$
 * -----------------------------------------------------------------------
 * @author		$Author$
 * @copyright	2006-2013 EQdkp-Plus Developer Team
 * @link		http://eqdkp-plus.com
 * @package		eqdkp-plus
 * @version		$Rev$
 * 
 * $Id$
 */
 
if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

class form extends gen_class {
	public static $shortcuts = array();
	// public static $singleton = false;
	
	public $form_id = '';		// the form_id is the identifier of the form, it should be unique and has otherwise no specific use
	
	public $use_tabs = false;
	public $use_fieldsets = false;
	public $use_dependency = false;
	
	public $lang_prefix = '';
	/* the language variables are build as follows:
	 *	- for tabs: $this->lang_prefix.'tab_'.$tabname
	 *	- for fieldsets: 
	 *		- $lang = $this->lang_prefix.'fs_'.$fieldsetname;
	 *		- $info = $this->lang_prefix.'fs_info_'.$fieldsetname;
	 *	- for fields:
	 *		- $lang = $this->lang_prefix.'f_'.$name;
	 *		- $help = $this->lang_prefix.'f_help_'.$name;
	 */
	
	private $field_array = array(); // tab => array(fieldset => array(field => array(options)), field => array(options), field => array(options))
	/*	the options of each field is an array containing the following elements
	 * 		- type: the type of the field to use (e.g. 'dropdown')
	 *		- any additional options for the chosen fieldtype
	 *		- optionally the following entries can be included
	 *			- 'text' 	=> 'text to put in front of the field'
	 *			- 'text2'	=> 'text to put behind the field'
	 *			- 'encrypt'	=> whether to encrypt the data of the field (encrypt on read, decrypt on output)
	 *			- 'lang'	=> if a custom language variable shall be used for the field
	 *			- 'help'	=> if a custom help-language variable shall be used
	 */
	
	// flags if dependency jquery stuff has been initialised
	private $jq_dropdown 	= false;
	private $jq_checkbox 	= false;
	private $jq_radio 		= false;
	
	public function __construct($form_id) {
		$this->form_id = $form_id;
	}
	
	/*  add a tab and put existing fieldsets and fields into it
	 *	@tab (string):		name of the tab, according to language variable "{lang_prefix}tab_{name}"
	 *	@fieldsets (array):	array with names of the fieldsets which shall be moved to this tab (from ungrouped)
	 *	@fields (array):	array with names of the fields which shall be moved to this tab (from ungrouped)
	 */
	public function add_tab($tab, $fieldsets=array(), $fields=array()) {
		foreach($fieldsets as $fieldset) {
			if(isset($this->field_array[$fieldset])) {
				$this->field_array[$tab][$fieldset] = $this->field_array['fs'][$fieldset];
				unset($this->field_array['fs'][$field]);
			}
		}
		foreach($fields as $field) {
			if(isset($this->field_array[$field])) {
				$this->field_array[$tab]['f'][$field] = $this->field_array['f'][$field];
				unset($this->field_array['f'][$field]);
			}
		}
	}
	
	/*	add multiple tabs at once
	 *	@fieldarray (array):	 tabname => array(fieldsetname => array(fieldname => array(options)))
	 */
	public function add_tabs($fieldarray) {
		$this->field_array = array_merge($this->field_array, $fieldarray);
	}
	
	/*	group existing fields in a fieldset
	 *	@fieldset (string):	name of the fieldset, according to language variable "{lang_prefix}fs_{name}"
	 *	@fields (array):	array with names of the fields to be moved
	 *	@tab (string):		name of the tab in which the fieldset shall be created (and the fields are currently located in)
	 */
	public function add_fieldset($fieldset, $fields=array(), $tab='') {
		foreach($fields as $field) {
			if($tab) {
				$this->field_array[$tab][$fieldset][$field] = $this->field_array[$tab]['f'][$field];
				unset($this->field_array[$tab]['f'][$field]);
			} else {
				$this->field_array['fs'][$fieldset][$field] = $this->field_array['f'][$field];
				unset($this->field_array['f'][$field]);
			}
		}
	}
	
	/*	add one or more fieldsets including its fields in array format
	 *	@fieldarray (array):	fieldsetname => array(fieldname => array(options))
	 *	@tab (string):			name of the tab into which the fieldset shall be put
	 */
	public function add_fieldsets($fieldarray, $tab='') {
		if($tab)
			$this->field_array[$tab] = array_merge($this->field_array[$tab], $fieldarray);
		else
			$this->field_array['fs'] = array_merge($this->field_array, $fieldarray);
	}
	
	/*	add a single field to the form
	 *	@name (string):		name of the field, according to language variable "{lang_prefix}{name}"
	 *	@options (array):	argument-array for the field
	 *	@fieldset (string):	name of the fieldset where the field shall be placed
	 *	@tab (string):		name of the tab where the field shall be placed
	 */
	public function add_field($name, $options, $fieldset='', $tab='') {
		if($tab && $fieldset)
			$this->field_array[$tab][$fieldset][$name] = $options;
		elseif($tab)
			$this->field_array[$tab]['f'][$name] = $options;
		elseif($fieldset)
			$this->field_array['fs'][$fieldset][$name] = $options;
		else
			$this->field_array['f'][$name] = $options;
	}
	
	/*	add multiple fields in array format
	 *	@fieldarray (array):	fieldname => array(options)
	 *	@fieldset (string):		name of the fieldset where the fields shall be placed
	 *	@tab (string):			name of the tab where the fields shall be placed
	 */
	public function add_fields($fieldarray, $fieldset='', $tab='') {
		if($tab && $fieldset) {
			if(empty($this->field_array[$tab][$fieldset])) $this->field_array[$tab][$fieldset] = $fieldarray;
			else $this->field_array[$tab][$fieldset] = array_merge($this->field_array[$tab][$fieldset], $fieldarray);
		} elseif($tab) {
			if(empty($this->field_array[$tab]['f'])) $this->field_array[$tab]['f'] = $fieldarray;
			else $this->field_array[$tab]['f'] = array_merge($this->field_array[$tab], $fieldarray);
		} elseif($fieldset) {
			if(empty($this->field_array['fs'][$fieldset])) $this->field_array['fs'][$fieldset] = $field_array;
			else $this->field_array['fs'][$fieldset] = array_merge($this->field_array[$fieldset], $fieldarray);
		} else {
			if(empty($this->field_array['f'])) $this->field_array['f'] = $field_array;
			else $this->field_array['f'] = array_merge($this->field_array['f'], $fieldarray);
		}
	}
	
	/*	assign output to template variables
	 *	@values (array):	key => value, array containing the values with which to fill the formular
	 */
	public function output($values=array()) {
		if($this->use_tabs) {
			$this->jquery->Tab_header($this->lang_prefix.'tabs', true);
			foreach($this->field_array as $tabname => $tabdata) {
				$this->tab2tpl($tabname);
				if($this->use_fieldsets) {
					foreach($tabdata as $fieldsetname => $fieldsetdata) {
						$this->fs2tpl($fieldsetname, 'tabs.fieldsets');
						foreach($fieldsetdata as $name => $options) {
							if(!isset($values[$name])) $values[$name] = '';
							$this->f2tpl($name, $options, 'tabs.fieldsets.fields', $values[$name]);
						}
					}
				} else {
					foreach($tabdata['f'] as $name => $options) {
						if(!isset($values[$name])) $values[$name] = '';
						$this->f2tpl($name, $options, 'tabs.fields', $values[$name]);
					}
				}
			}
		} else {
			if($this->use_fieldsets) {
				foreach($this->field_array['fs'] as $fieldsetname => $fieldsetdata) {
					$this->fs2tpl($fieldsetname);
					foreach($fieldsetdata as $name => $options) {
						if(!isset($values[$name])) $values[$name] = '';
						$this->f2tpl($name, $options, 'fieldsets.fields', $values[$name]);
					}
				}
			} else {
				foreach($this->field_array['f'] as $name => $options) {
					if(!isset($values[$name])) $values[$name] = '';
					$this->f2tpl($name, $options, 'fields', $values[$name]);
				}
			}
		}
		$this->tpl->assign_var('FORM_ID', $this->form_id);
	}
	
	/*	read input data according to form-fields
	 *	@return (array):	inputname => value
	 */
	public function return_values() {
		$values = array();
		foreach($this->field_array as $tabname => $fieldsets) {
			// extra handling for the only case of a 2-deep array
			if($tabname == 'f') {
				// variable fieldsets holds fields in this case
				foreach($fieldsets as $name => $options) {
					$values[$name] = register('h'.$options['type'], array($name, $options))->inpval();
					if(!empty($options['encrypt'])) $values[$name] = $this->encrypt->encrypt($values[$name]);
				}
				continue;
			}
			foreach($fieldsets as $fields) {
				foreach($fields as $name => $options) {
					if(!registry::class_exists('h'.$options['type'])) {
						#var_dump($name);
						continue;
					}
					$field_class = 'h'.$options['type'];
					$field_class = new $field_class($name, $options);
					$values[$name] = $field_class->inpval();
					if(!empty($options['encrypt'])) $values[$name] = $this->encrypt->encrypt($values[$name]);
				}
			}
		}
		return $values;
	}
	
	private function tab2tpl($tabname) {
		$this->tpl->assign_block_vars('tabs', array(
			'NAME'	=> $this->user->lang($this->lang_prefix.'tab_'.$tabname),
			'ID'	=> $tabname
			)
		);
	}
	
	private function fs2tpl($fieldsetname, $key) {
		$lang = $this->lang_prefix.'fs_'.$fieldsetname;
		$info = $this->lang_prefix.'fs_info_'.$fieldsetname;
		$this->tpl->assign_block_vars($key, array(
			'NAME'		=> ($this->user->lang($lang, false, false)) ? $this->user->lang($lang) : $this->game->glang($lang),
			'INFO'		=> ($this->user->lang($info, false, false)) ? $this->user->lang($info) : $this->game->glang($info),
		));
	}
	
	private function f2tpl($name, $options, $key, $value) {
		// TODO: check 'disabled'
		
		// choose language var
		if(!isset($options['lang'])) {
			$lang = $this->lang_prefix.'f_'.$name;
			$help = $this->lang_prefix.'f_help_'.$name;
		} else {
			$lang = $options['lang'];
			if(!isset($options['help'])) $help = $lang.'_help';
			else $help = $options['help'];
		}
		
		// encryption
		if(!empty($options['encrypt'])) $value = $this->encrypt->decrypt($value);
		
		// fill in the field
		if(!empty($value)) $options['value'] = $value;
		
		// additional text around field?
		$text = (empty($options['text'])) ? '' : $options['text'];
		$text2 = (empty($options['text2'])) ? '' : $options['text2'];
		
		// dependency stuff - hide other elements depening on selection
		if(!empty($options['dependency'])) $this->jq_dep_init($options['type']);
		
		$field_class = 'h'.$options['type'];
		$field = (registry::class_exists('h'.$options['type'])) ?  new $field_class($name, $options) : '';
		$this->tpl->assign_block_vars($key, array(
			'NAME'		=> ($this->user->lang($lang, false, false)) ? $this->user->lang($lang) : (($this->game->glang($lang)) ? $this->game->glang($lang) : $name),
			'HELP'		=> ($this->user->lang($help, false, false)) ? $this->user->lang($help) : (($this->game->glang($help)) ? $this->game->glang($help) : ''),
			'FIELD'		=> $text.$field.$text2
		));
	}
	
	private function jq_dep_init($type) {
		if($this->{'jq_'.$type}) return;
		switch($type) {
			case 'dropdown':
				$js = "
	$('.form_change').change(function(){
		$.each($(this).find('option'), function(){
			var selected = this.selected;
			$.each($(this).data('form-change').split(','), function(index, value){
				if(selected){
					$('#".$this->form_id."').find('input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"]').removeAttr('disabled');
					$('#".$this->form_id."').find('dl:has(input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"])').show();
				}else{
					$('#".$this->form_id."').find('input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"]').attr('disabled', 'disabled');
					$('#".$this->form_id."').find('dl:has(input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"])').hide();
				}
			});
		});
	}).trigger('change');";
				break;
				
			case 'checkbox':
			case 'radio':
				$js = "
	$('.form_change_checkbox, .form_change_radio').change(function(){
		$.each($('.form_change_checkbox > input, .form_change_radio > input'), function(){
			var checked = this.checked;
			$.each($(this).data('form-change').split(','), function(index, value){
				if(checked){
					$('#".$this->form_id."').find('input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"]').removeAttr('disabled');
					$('#".$this->form_id."').find('dl:has(input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"])').show();
				}else{
					$('#".$this->form_id."').find('input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"]').attr('disabled', 'disabled');
					$('#".$this->form_id."').find('dl:has(input[name=\"'+value+'\"],select[name=\"'+value+'\"],textarea[name=\"'+value+'\"])').hide();
				}
			});
		});
	}).trigger('change');";
				break;
				
			default: return;
		}
		$this->{'jq_'.$type} = true;
		$this->tpl->add_js($js, 'docready');
	}
}
?>