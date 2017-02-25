Feature: Bootstrapping
  In order to inject custom code during the application bootstrap process
  As a developer
  I need to select the files to be bootstrapped

  Scenario: No files are selected to bootstrap
    Given the "index.php" file contains:
      """
      <?php
      echo "My legacy application";
      """
    And the file "index.php" is configured as the unique frontend controller
    When I make a request to "/item"
    Then the page content should be "My legacy application"

  Scenario: One file is selected to bootstrap
    Given the "index.php" file contains:
      """
      <?php
      echo "My legacy application";
      """
    And the "bootstrap.php" file contains:
      """
      <?php
      echo "Bootstrap file. ";
      """
    And the file "index.php" is configured as the unique frontend controller
    And the file "bootstrap.php" is configured to bootstrap with the application
    When I make a request to "/item"
    Then the page content should be "Bootstrap file. My legacy application"
