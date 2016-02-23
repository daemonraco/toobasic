<?php

class FormsBuilderBasicTypeTest extends TooBasic_SeleniumTestCase {
	//
	// Internal properties.
	protected $_formName = 'tform';
	protected $_moduleName = 'mymodule';
	//
	// Creation and edition @{
	public function testCreateAForm() {
		$this->runFormCommand("php shell.php sys forms new {$this->_formName} -M {$this->_moduleName}", "Creating form '{$this->_formName}':(.*)Done(.*)\(Path: /(.*)/modules/mymodule/forms/{$this->_formName}\.json\)");
		$this->runFormCommand("php shell.php sys forms --set-name 'test_form' -f {$this->_formName}", "Setting form '{$this->_formName}' name:(.*)Done");
		$this->runFormCommand("php shell.php sys forms --set-action '#' -f {$this->_formName}", "Setting form '{$this->_formName}' action:(.*)Done");
		$this->runFormCommand("php shell.php sys forms --set-action '?action=somewhere' -f {$this->_formName} -m create", "Setting form '{$this->_formName}' action \(for mode 'create'\):(.*)Done");
		$this->runFormCommand("php shell.php sys forms --set-method 'get' -f {$this->_formName}", "Setting form '{$this->_formName}' method:(.*)Done");
		$this->runFormCommand("php shell.php sys forms --set-method 'post' -f {$this->_formName} -m create", "Setting form '{$this->_formName}' method \(for mode 'create'\):(.*)Done");
		$this->runFormCommand("php shell.php sys forms --set-type 'table' -f {$this->_formName}", "Setting form '{$this->_formName}' type:(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-field name -f {$this->_formName} -t input", "Adding field 'name' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-field age -f {$this->_formName} -t input", "Adding field 'age' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-field status -f {$this->_formName} -t enum:ACTIVE:INACTIVE:REMOVED:UNKNOWN -v ACTIVE", "Adding field 'status' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-field description -f {$this->_formName} -t text", "Adding field 'description' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --set-attribute class -f {$this->_formName} -v form", "Setting form '{$this->_formName}' attribute 'class':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --set-attribute ng-non-bindable -f {$this->_formName} -yes", "Setting form '{$this->_formName}' attribute 'ng-non-bindable':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-button send -f {$this->_formName} -t submit", "Adding button 'send' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-button reset -f {$this->_formName} -t reset", "Adding button 'reset' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-button back -f {$this->_formName} -t button", "Adding button 'back' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-button create -f {$this->_formName} -t submit -m create", "Adding button 'create' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-button reset -f {$this->_formName} -t reset -m create", "Adding button 'reset' to form '{$this->_formName}':(.*)Done");
		$this->runFormCommand("php shell.php sys forms --add-button back -f {$this->_formName} -t button -m create", "Adding button 'back' to form '{$this->_formName}':(.*)Done");
	}
	// @}
	//
	// Analysis @{
	public function testLoadingFormInViewMode() {
		$this->url("?action=test&form={$this->_formName}&form_mode=view");
		debugit($this->source());
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
		$this->runFormCommand("php shell.php sys forms rm {$this->_formName}", "Removing form '{$this->_formName}':(.*)Done(.*)\(Path: /(.*)/modules/mymodule/forms/{$this->_formName}\.json\)");
	}
	// @}
	//
	// Internal methods @{
	protected function assertFormBuilderRender() {
		$this->checkCurrentSource();

		$src = $this->source();
		$this->assertNotRegExp('/FormsException:/m', $src, 'There seems to be an error rendering the form');
	}
	protected function runFormCommand($command, $expected) {
		$output = $this->runCommand($command);
		$this->assertRegExp("#{$expected}#m", $output, 'The command returned an unexpected value.');
	}
	protected function validateCreateAndEditContents($isCreate = false) {
		$this->assertFormBuilderRender();
		//
		// Checking form tag
		$form = $this->byId('test_form');
		$this->assertTrue(boolval($form), 'Unable to point the built form.');

		//
		// Checking form tag
		$this->assertNotNull($form->attribute('action'), "Tag 'form' doesn't have the attribute 'action'.");
		$pattern = "~^({$this->url()}|)#$~";
		if($isCreate) {
			$pattern = "~?action=somewhere$~";
		}
		$this->assertRegExp(str_replace('?', '\\?', $pattern), $form->attribute('action'), "Tag 'form' attribute 'action' has an unexpected value.");
		$this->assertNotNull($form->attribute('method'), "Tag 'form' doesn't have the attribute 'method'.");
		$this->assertEquals($isCreate ? 'post' : 'get', $form->attribute('method'), "Tag 'form' attribute 'method' has an unexpected value.");
		$this->assertNotNull($form->attribute('class'), "Tag 'form' doesn't have the attribute 'class'.");
		$this->assertEquals('form', $form->attribute('class'), "Tag 'form' attribute 'class' has an unexpected value.");
		$this->assertNotNull($form->attribute('ng-non-bindable'), "Tag 'form' doesn't have the attribute 'ng-non-bindable'.");
		//
		// Checking labels.
		foreach(['name', 'age', 'status', 'description'] as $name) {
			$label = $form->elements($this->using('css selector')->value("label[for='test_form_{$name}']"));
			$this->assertEquals(1, count($label), "Label for field '{$name}' was not found.");
			$this->assertEquals("@label_formcontrol_{$name}", $label[0]->text(), "Label for field '{$name}' has an unexpected content.");
		}
		//
		// Checking fields @{
		//
		// Field 'name'.
		$field = $form->elements($this->using('css selector')->value('input#test_form_name'));
		$this->assertEquals(1, count($field), "Field 'name' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'name' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'name' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'name' attribute 'name' is not present.");
		$this->assertEquals('name', $field[0]->attribute('name'), "Field 'name' attribute 'name' has an unexpected value.");
		$this->assertNull($field[0]->attribute('readonly'), "Field 'name' attribute 'readonly' is present.");
		$this->assertEquals($isCreate ? '' : 'John Doe', $field[0]->value(), "Field 'name' value is not as expected.");
		//
		// Field 'description'.
		$field = $form->elements($this->using('css selector')->value('textarea#test_form_description'));
		$this->assertEquals(1, count($field), "Field 'description' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'description' attribute 'name' is not present.");
		$this->assertEquals('description', $field[0]->attribute('name'), "Field 'description' attribute 'name' has an unexpected value.");
		$this->assertNull($field[0]->attribute('readonly'), "Field 'description' attribute 'readonly' is present.");
		$this->assertEquals($isCreate ? '' : 'Someone who works somewhere.', $field[0]->value(), "Field 'description' value is not as expected.");
		//
		// Field 'status'.
		$field = $form->elements($this->using('css selector')->value('select#test_form_status'));
		$this->assertEquals(1, count($field), "Field 'status' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'status' attribute 'name' is not present.");
		$this->assertEquals('status', $field[0]->attribute('name'), "Field 'status' attribute 'name' has an unexpected value.");
		$this->assertNull($field[0]->attribute('readonly'), "Field 'status' attribute 'readonly' is present.");
		$options = $field[0]->elements($this->using('css selector')->value('option'));
		$this->assertEquals(4, count($options), "The amount of options for field 'status' is unexpected.");
		foreach(['ACTIVE', 'INACTIVE', 'REMOVED', 'UNKNOWN'] as $value) {
			$option = $field[0]->elements($this->using('css selector')->value("option[value='{$value}']"));
			$this->assertEquals(1, count($option), "The amount of options for field 'status' with value '{$value}' is unexpected.");
			$this->assertEquals("@select_option_{$value}", $option[0]->text(), "Field 'status' option with value '{$value}' has an unexpected text.");
		}
		$selectedOptions = $field[0]->elements($this->using('css selector')->value('option[selected]'));
		$this->assertEquals(1, count($selectedOptions), "The amount of selected options for field 'status' is unexpected.");
		$this->assertEquals($isCreate ? 'ACTIVE' : 'REMOVED', $selectedOptions[0]->value(), "The default selected option for field 'status' is incorrect.");
		//
		// Field 'age'.
		$field = $form->elements($this->using('css selector')->value('input#test_form_age'));
		$this->assertEquals(1, count($field), "Field 'age' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'age' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'age' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'age' attribute 'name' is not present.");
		$this->assertEquals('age', $field[0]->attribute('name'), "Field 'age' attribute 'name' has an unexpected value.");
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
		if($isCreate) {
			$this->assertNotNull($buttons[0]->attribute('id'), "Send button attribute 'id' is not present.");
			$this->assertEquals('test_form_create', $buttons[0]->attribute('id'), "Send button attribute 'id' has an unexpected value.");
			$this->assertNotNull($buttons[0]->attribute('type'), "Send button attribute 'type' is not present.");
			$this->assertEquals('submit', $buttons[0]->attribute('type'), "Send button attribute 'type' has an unexpected value.");
			$this->assertEquals('@btn_create', $buttons[0]->text(), "Send button label has an unexpected value.");
		} else {
			$this->assertNotNull($buttons[0]->attribute('id'), "Create button attribute 'id' is not present.");
			$this->assertEquals('test_form_send', $buttons[0]->attribute('id'), "Create button attribute 'id' has an unexpected value.");
			$this->assertNotNull($buttons[0]->attribute('type'), "Create button attribute 'type' is not present.");
			$this->assertEquals('submit', $buttons[0]->attribute('type'), "Create button attribute 'type' has an unexpected value.");
			$this->assertEquals('@btn_send', $buttons[0]->text(), "Create button label has an unexpected value.");
		}
		//
		// Reset button.
		$this->assertNotNull($buttons[1]->attribute('id'), "Reset button attribute 'id' is not present.");
		$this->assertEquals('test_form_reset', $buttons[1]->attribute('id'), "Reset button attribute 'id' has an unexpected value.");
		$this->assertNotNull($buttons[1]->attribute('type'), "Reset button attribute 'type' is not present.");
		$this->assertEquals('reset', $buttons[1]->attribute('type'), "Reset button attribute 'type' has an unexpected value.");
		$this->assertEquals('@btn_reset', $buttons[1]->text(), "Reset button label has an unexpected value.");
		//
		// Go back button.
		$this->assertNotNull($buttons[2]->attribute('id'), "Back button attribute 'id' is not present.");
		$this->assertEquals('test_form_back', $buttons[2]->attribute('id'), "Back button attribute 'id' has an unexpected value.");
		$this->assertNotNull($buttons[2]->attribute('type'), "Back button attribute 'type' is not present.");
		$this->assertEquals('button', $buttons[2]->attribute('type'), "Back button attribute 'type' has an unexpected value.");
		$this->assertEquals('@btn_back', $buttons[2]->text(), "Back button label has an unexpected value.");
		// @}
	}
	protected function validateViewAndRemove() {
		$this->assertFormBuilderRender();
		//
		// Checking form tag
		$form = $this->byId('test_form');
		$this->assertTrue(boolval($form), 'Unable to point the built form.');
		$this->assertNotNull($form->attribute('action'), "Tag 'form' doesn't have the attribute 'action'.");
		$this->assertRegExp('~^('.str_replace('?', '\\?', $this->url()).'|)#$~', $form->attribute('action'), "Tag 'form' attribute 'action' has an unexpected value.");
		$this->assertNotNull($form->attribute('method'), "Tag 'form' doesn't have the attribute 'method'.");
		$this->assertEquals('get', $form->attribute('method'), "Tag 'form' attribute 'method' has an unexpected value.");
		$this->assertNotNull($form->attribute('class'), "Tag 'form' doesn't have the attribute 'class'.");
		$this->assertEquals('form', $form->attribute('class'), "Tag 'form' attribute 'class' has an unexpected value.");
		$this->assertNotNull($form->attribute('ng-non-bindable'), "Tag 'form' doesn't have the attribute 'ng-non-bindable'.");
		//
		// Checking labels.
		foreach(['name', 'age', 'status', 'description'] as $name) {
			$label = $form->elements($this->using('css selector')->value("label[for='test_form_{$name}']"));
			$this->assertEquals(1, count($label), "Label for field '{$name}' was not found.");
			$this->assertEquals("@label_formcontrol_{$name}", $label[0]->text(), "Label for field '{$name}' has an unexpected content.");
		}
		//
		// Checking fields @{
		//
		// Field 'name'.
		$field = $form->elements($this->using('css selector')->value('input#test_form_name'));
		$this->assertEquals(1, count($field), "Field 'name' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'name' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'name' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'name' attribute 'name' is not present.");
		$this->assertEquals('name', $field[0]->attribute('name'), "Field 'name' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'name' attribute 'readonly' is not present.");
		$this->assertEquals('John Doe', $field[0]->value(), "Field 'name' value is not as expected.");
		//
		// Field 'description'.
		$field = $form->elements($this->using('css selector')->value('textarea#test_form_description'));
		$this->assertEquals(1, count($field), "Field 'description' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'description' attribute 'name' is not present.");
		$this->assertEquals('description', $field[0]->attribute('name'), "Field 'description' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'description' attribute 'readonly' is not present.");
		$this->assertEquals('Someone who works somewhere.', $field[0]->value(), "Field 'description' value is not as expected.");
		//
		// Field 'status'.
		$field = $form->elements($this->using('css selector')->value('input#test_form_status'));
		$this->assertEquals(1, count($field), "Field 'status' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'status' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'status' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'status' attribute 'name' is not present.");
		$this->assertEquals('status', $field[0]->attribute('name'), "Field 'status' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'status' attribute 'readonly' is not present.");
		$this->assertEquals('@select_option_REMOVED', $field[0]->value(), "Field 'status' value is not as expected.");
		//
		// Field 'age'.
		$field = $form->elements($this->using('css selector')->value('input#test_form_age'));
		$this->assertEquals(1, count($field), "Field 'age' is not present, it doesnt have the right tag, or there are more than one.");
		$this->assertNotNull($field[0]->attribute('type'), "Field 'age' attribute 'type' is not present.");
		$this->assertEquals('text', $field[0]->attribute('type'), "Field 'age' attribute 'type' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('name'), "Field 'age' attribute 'name' is not present.");
		$this->assertEquals('age', $field[0]->attribute('name'), "Field 'age' attribute 'name' has an unexpected value.");
		$this->assertNotNull($field[0]->attribute('readonly'), "Field 'age' attribute 'readonly' is not present.");
		$this->assertEquals('36', $field[0]->value(), "Field 'age' value is not as expected.");
		// @}
		//
		// Checking buttons.
		$this->validateFormButtons($form);
	}
	// @}
}
