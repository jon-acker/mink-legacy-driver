Feature: Passing parameters
  In order to emulate ...
  As a developer
  I need to be able to pass GET and POST parameters to the application

  Background:
    Given the file "index.php" is configured as the unique frontend controller

  Scenario: Error is generated when trying to render input when no parameters passed
    Given the "index.php" file contains:
      """
      <?php
      echo "My name is: {$_GET['name']}";
      """
    When I make a request to "/item" with no parameters
    Then the page content should include the error "Undefined index: name"

  Scenario: Error is generated when trying to render input with incorrect parameter passed
    Given the "index.php" file contains:
      """
      <?php
      echo "My name is: {$_GET['name']}";
      """
    When I make a request to "/item?nae=jon"
    Then the page content should include the error "Undefined index: name"

  Scenario: Correctly rendering input when correct parameter passed
    Given the "index.php" file contains:
      """
      <?php
      echo "My name is: {$_GET['name']}";
      """
    When I make a request to "/?name=jon"
    Then the page content should be "My name is: jon"