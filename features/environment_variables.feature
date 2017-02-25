Feature: Environment variables
  In order to emulate environmental conditions during testing
  As a developer
  I need to inject environment variables

  Scenario: No environment variables required
    Given the "index.php" file contains:
      """
      <?php
      echo "My legacy application";
      """
    And the file "index.php" is configured as the unique frontend controller
    When I make a request to "/item"
    Then the page content should be "My legacy application"

  Scenario: Environment variable is required
    Given the "index.php" file contains:
      """
      <?php
      echo "My " . getenv("app_name") . " application";
      """
    And the file "index.php" is configured as the unique frontend controller
    And the enviornment variable "app_name" is set to "legacy"
    When I make a request to "/item"
    Then the page content should be "My legacy application"
