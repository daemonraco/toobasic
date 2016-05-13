<?php

class FormsBuilderQuickSysToolTest extends TooBasic_SeleniumTestCase {
	//
	// Internal properties.
	protected $_formName = 'myform';
	protected $_moduleName = 'mymodule';
	//
	// Creation and edition @{
	public function testCreateAForm() {
		$cmd = "php shell.php sys qforms new {$this->_formName}";
		$cmd.= " -m post";
		$cmd.= " -t bootstrap";
		$cmd.= " -M {$this->_moduleName}";
		$cmd.= " -f name:input";
		$cmd.= " -f description:text";
		$cmd.= " -f age:input";
		$cmd.= " -f status:enum:ACTIVE:INACTIVE:BANNED";
		$cmd.= " -b send:submit";
		$cmd.= " -b reset:reset";
		$cmd.= " -b back:button";
		$cmd.= " -bx thin";
		$cmd.= " -bx bcolors";
		$output = $this->runCommand($cmd);
		$this->assertRegExp("#Creating form '{$this->_formName}':(.*)Done(.*)\(Path: /(.*)/modules/mymodule/forms/{$this->_formName}\.json\)#m", $output, 'The command returned an unexpected value.');
	}
	// @}
	//
	// Analysis @{
	public function testLoadingFormInViewMode() {
		$this->url("?action=test&form={$this->_formName}&form_mode=view");
		$this->validateViewAndRemove();
	}
	public function testLoadingFormInCreateMode() {
		$this->url("?action=test&form={$this->_formName}&form_mode=create");
		$this->validateCreateAndEditContents(true);
	}
	public function testLoadingFormInEditMode() {
		$this->url("?action=test&form={$this->_formName}&form_mode=edit");
		$this->validateCreateAndEditContents();
	}
	public function testLoadingFormInRemoveMode() {
		$this->url("?action=test&form={$this->_formName}&form_mode=remove");
		$this->validateViewAndRemove();
	}
	// @}
	//
	// Description @{
	public function testDesribingForm() {
		$this->runCommand("php shell.php sys forms --describe {$this->_formName}");
	}
	// @}
	//
	// Removal @{
	public function testRemovingForm() {
		$output = $this->runCommand("php shell.php sys forms rm {$this->_formName}");
		$this->assertRegExp("#Removing form '{$this->_formName}':(.*)Done(.*)\(Path: /(.*)/modules/mymodule/forms/{$this->_formName}\.json\)#m", $output, 'The command returned an unexpected value.');
	}
	// @}
	//
	// Internal methods @{
	protected function assertFormBuilderRender() {
		$this->checkCurrentSource();

		$src = $this->source();
		$this->assertNotRegExp('/FormsException:/m', $src, 'There seems to be an error rendering the form');
	}
	protected function validateCreateAndEditContents($isCreate = false) {
		$this->assertFormBuilderRender();
		//
		// Checking form tag
		$form = $this->byId($this->_formName);
		$this->assertTrue(boolval($form), 'Unable to point the built form.');
		//
		// Checking form tag
		$this->assertNotNull($form->attribute('action'), "Tag 'form' doesn't have the attribute 'action'.");
		$this->assertRegExp(str_replace('?', '\\?', "~^({$this->url()}|)#$~"), $form->attribute('action'), "Tag 'form' attribute 'action' has an unexpected value.");
		$this->assertNotNull($form->attribute('method'), "Tag 'form' doesn't have the attribute 'method'.");
		$this->assertEquals('post', $form->attribute('method'), "Tag 'form' attribute 'method' has an unexpected value.");
		//
		// Checking labels.
		foreach(['name', 'age', 'status', 'description'] as $name) {
			$label = $form->elements($this->using('css selector')->value("label[for='{$this->_formName}_{$name}']"));
			$this->assertEquals(1, count($label), "Label for field '{$name}' was not found.");
			$this->assertEquals("@label_formcontrol_{$name}", $label[0]->text(), "Label for field '{$name}' has an unexpected content.");
		}
		//
		// Checking fields @{
		//
		// Field 'name'.
		$field = $form->elements($this->using('css selector')->value("input#{$this->_formName}_name"));
		$this->assertEquals(1, count($field), "Field 'name' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'name' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'name' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'name' attribute 'name' is not present.");
		$this->assertEquals('name', $field[0]->attribute('name'), "Field 'name' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'name' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'name' attribute 'class' has an unexpected value.");
		$this->assertNull($field[0]->attribute('readonly'), "Field 'name' attribute 'readonly' is present.");
		$this->assertEquals($isCreate ? '' : 'John Doe', $field[0]->value(), "Field 'name' value is not as expected.");
		//
		// Field 'description'.
		$field = $form->elements($this->using('css selector')->value("textarea#{$this->_formName}_description"));
		$this->assertEquals(1, count($field), "Field 'description' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'description' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'description' attribute 'class' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'description' attribute 'name' is not present.");
		$this->assertEquals('description', $field[0]->attribute('name'), "Field 'description' attribute 'name' has an unexpected value.");
		$this->assertNull($field[0]->attribute('readonly'), "Field 'description' attribute 'readonly' is present.");
		$this->assertEquals($isCreate ? '' : 'Someone who works somewhere.', $field[0]->value(), "Field 'description' value is not as expected.");
		//
		// Field 'status'.
		$field = $form->elements($this->using('css selector')->value("select#{$this->_formName}_status"));
		$this->assertEquals(1, count($field), "Field 'status' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'status' attribute 'name' is not present.");
		$this->assertEquals('status', $field[0]->attribute('name'), "Field 'status' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'status' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'status' attribute 'class' has an unexpected value.");
		$this->assertNull($field[0]->attribute('readonly'), "Field 'status' attribute 'readonly' is present.");
		$options = $field[0]->elements($this->using('css selector')->value('option'));
		$this->assertEquals(3, count($options), "The amount of options for field 'status' is unexpected.");
		foreach(['ACTIVE', 'INACTIVE', 'BANNED'] as $value) {
			$option = $field[0]->elements($this->using('css selector')->value("option[value='{$value}']"));
			$this->assertEquals(1, count($option), "The amount of options for field 'status' with value '{$value}' is unexpected.");
			$this->assertEquals("@select_option_{$value}", $option[0]->text(), "Field 'status' option with value '{$value}' has an unexpected text.");
		}
		if(!$isCreate) {
			$selectedOptions = $field[0]->elements($this->using('css selector')->value('option[selected]'));
			$this->assertEquals(1, count($selectedOptions), "The amount of selected options for field 'status' is unexpected.");
			$this->assertEquals('BANNED', $selectedOptions[0]->value(), "The default selected option for field 'status' is incorrect.");
		}
		//
		// Field 'age'.
		$field = $form->elements($this->using('css selector')->value("input#{$this->_formName}_age"));
		$this->assertEquals(1, count($field), "Field 'age' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'age' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'age' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'age' attribute 'name' is not present.");
		$this->assertEquals('age', $field[0]->attribute('name'), "Field 'age' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'age' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'age' attribute 'class' has an unexpected value.");
		$this->assertNull($field[0]->attribute('readonly'), "Field 'age' attribute 'readonly' is present.");
		$this->assertEquals($isCreate ? '' : '36', $field[0]->value(), "Field 'age' value is not as expected.");
		// @}
		//
		// Checking buttons.
		$this->validateFormButtons($form, $isCreate);
	}
	protected function validateFormButtons($form, $isCreate = false) {
		$buttons = $form->elements($this->using('css selector')->value('button'));
		$this->assertEquals(3, count($buttons), 'The amount of buttons in the form is not as expected.');
		//
		// Send button.
		$this->assertNotNull($buttons[0]->attribute('class'), "Create button attribute 'class' is not present.");
		$this->assertEquals('btn btn-sm btn-success', $buttons[0]->attribute('class'), "Create button attribute 'class' has an unexpected value.");
		$this->assertNotNull($buttons[0]->attribute('id'), "Create button attribute 'id' is not present.");
		$this->assertEquals("{$this->_formName}_send", $buttons[0]->attribute('id'), "Create button attribute 'id' has an unexpected value.");
		$this->assertNotNull($buttons[0]->attribute('type'), "Create button attribute 'type' is not present.");
		$this->assertEquals('submit', $buttons[0]->attribute('type'), "Create button attribute 'type' has an unexpected value.");
		$this->assertEquals('@btn_send', $buttons[0]->text(), "Create button label has an unexpected value.");
		//
		// Reset button.
		$this->assertNotNull($buttons[1]->attribute('class'), "Reset button attribute 'class' is not present.");
		$this->assertEquals('btn btn-sm btn-default', $buttons[1]->attribute('class'), "Reset button attribute 'class' has an unexpected value.");
		$this->assertNotNull($buttons[1]->attribute('id'), "Reset button attribute 'id' is not present.");
		$this->assertEquals("{$this->_formName}_reset", $buttons[1]->attribute('id'), "Reset button attribute 'id' has an unexpected value.");
		$this->assertNotNull($buttons[1]->attribute('type'), "Reset button attribute 'type' is not present.");
		$this->assertEquals('reset', $buttons[1]->attribute('type'), "Reset button attribute 'type' has an unexpected value.");
		$this->assertEquals('@btn_reset', $buttons[1]->text(), "Reset button label has an unexpected value.");
		//
		// Go back button.
		$this->assertNotNull($buttons[2]->attribute('class'), "Back button attribute 'class' is not present.");
		$this->assertEquals('btn btn-sm btn-default', $buttons[2]->attribute('class'), "Back button attribute 'class' has an unexpected value.");
		$this->assertNotNull($buttons[2]->attribute('id'), "Back button attribute 'id' is not present.");
		$this->assertEquals("{$this->_formName}_back", $buttons[2]->attribute('id'), "Back button attribute 'id' has an unexpected value.");
		$this->assertNotNull($buttons[2]->attribute('type'), "Back button attribute 'type' is not present.");
		$this->assertEquals('button', $buttons[2]->attribute('type'), "Back button attribute 'type' has an unexpected value.");
		$this->assertEquals('@btn_back', $buttons[2]->text(), "Back button label has an unexpected value.");
		// @}
	}
	protected function validateViewAndRemove() {
		$this->assertFormBuilderRender();
		//
		// Checking form tag
		$form = $this->byId($this->_formName);
		$this->assertTrue(boolval($form), 'Unable to point the built form.');
		$this->assertNotNull($form->attribute('action'), "Tag 'form' doesn't have the attribute 'action'.");
		$this->assertRegExp('~^('.str_replace('?', '\\?', $this->url()).'|)#$~', $form->attribute('action'), "Tag 'form' attribute 'action' has an unexpected value.");
		$this->assertNotNull($form->attribute('method'), "Tag 'form' doesn't have the attribute 'method'.");
		$this->assertEquals('post', $form->attribute('method'), "Tag 'form' attribute 'method' has an unexpected value.");
		//
		// Checking labels.
		foreach(['name', 'age', 'status', 'description'] as $name) {
			$label = $form->elements($this->using('css selector')->value("label[for='{$this->_formName}_{$name}']"));
			$this->assertEquals(1, count($label), "Label for field '{$name}' was not found.");
			$this->assertEquals("@label_formcontrol_{$name}", $label[0]->text(), "Label for field '{$name}' has an unexpected content.");
		}
		//
		// Checking fields @{
		//
		// Field 'name'.
		$field = $form->elements($this->using('css selector')->value("input#{$this->_formName}_name"));
		$this->assertEquals(1, count($field), "Field 'name' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'name' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'name' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'name' attribute 'name' is not present.");
		$this->assertEquals('name', $field[0]->attribute('name'), "Field 'name' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'name' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'name' attribute 'class' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'name' attribute 'readonly' is not present.");
		$this->assertEquals('John Doe', $field[0]->value(), "Field 'name' value is not as expected.");
		//
		// Field 'description'.
		$field = $form->elements($this->using('css selector')->value("textarea#{$this->_formName}_description"));
		$this->assertEquals(1, count($field), "Field 'description' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'description' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'description' attribute 'class' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'description' attribute 'name' is not present.");
		$this->assertEquals('description', $field[0]->attribute('name'), "Field 'description' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'description' attribute 'readonly' is not present.");
		$this->assertEquals('Someone who works somewhere.', $field[0]->value(), "Field 'description' value is not as expected.");
		//
		// Field 'status'.
		$field = $form->elements($this->using('css selector')->value("input#{$this->_formName}_status"));
		$this->assertEquals(1, count($field), "Field 'status' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'status' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'status' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'status' attribute 'name' is not present.");
		$this->assertEquals('status', $field[0]->attribute('name'), "Field 'status' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'status' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'status' attribute 'class' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'status' attribute 'readonly' is not present.");
		$this->assertEquals('@select_option_BANNED', $field[0]->value(), "Field 'status' value is not as expected.");
		//
		// Field 'age'.
		$field = $form->elements($this->using('css selector')->value("input#{$this->_formName}_age"));
		$this->assertEquals(1, count($field), "Field 'age' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'age' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'age' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'age' attribute 'name' is not present.");
		$this->assertEquals('age', $field[0]->attribute('name'), "Field 'age' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('class'), "Field 'age' attribute 'class' is not present.");
		$this->assertEquals('form-control input-sm', $field[0]->attribute('class'), "Field 'age' attribute 'class' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'age' attribute 'readonly' is not present.");
		$this->assertEquals('36', $field[0]->value(), "Field 'age' value is not as expected.");
		// @}
		//
		// Checking buttons.
		$this->validateFormButtons($form);
	}
	// @}
}
