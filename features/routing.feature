Feature: Routing
  In order to execute the correct files
  As a developer
  I need to route the request to the required frontend controller

  Scenario: No routing required
    Given the "index.php" file contains:
      """
      <?php
      echo "My legacy application";
      """
    And the file "index.php" is configured as the unique frontend controller
    When I make a request to "/item"
    Then the page content should be "My legacy application"

  Scenario: Single file routing
    Given the "index.php" file contains:
      """
      <?php
      $uri = $_SERVER["REQUEST_URI"];
      switch ($uri) {
          case "/user":
              echo "This is the user section";
              break;
          case "/item":
              echo "Here is the requested item";
              break;
      }
      """
    And the file "index.php" is configured as the unique frontend controller
    When I make a request to "/item"
    Then the page content should be "Here is the requested item"

  Scenario: Multiple files routing
    Given the "user.php" file contains:
      """
      <?php
      echo "This is the user section";
      """
    And the "item.php" file contains:
      """
      <?php
      echo "Here is the requested item";
      """
    And the file "user.php" is configured as the frontend controller for "/user"
    And the file "item.php" is configured as the frontend controller for "/item"
    When I make a request to "/user"
    Then the page content should be "This is the user section"

  Scenario: Method-based routing
    Given the "add_user.php" file contains:
      """
      <?php
      echo "User was successfully added";
      """
    And the "get_user.php" file contains:
      """
      <?php
      echo "Here is the requested user";
      """
    And the file "add_user.php" is configured as the frontend controller for "/user" and method "POST"
    And the file "get_user.php" is configured as the frontend controller for "/user" and method "GET"
    When I make a "POST" request to "/user"
    Then the page content should be "User was successfully added"

  Scenario: Parameters-based routing
    Given the "search_by_id.php" file contains:
      """
      <?php
      echo "The item with the requested ID was found";
      """
    And the "search_by_name.php" file contains:
      """
      <?php
      echo "The item with the requested name was found";
      """
    And the file "search_by_id.php" is configured as the frontend controller for "/item/{item}" with parameter "item" allowed to be "\d+"
    And the file "search_by_name.php" is configured as the frontend controller for "/item/{item}" with parameter "item" allowed to be "\D+"
    When I make a request to "/item/123"
    Then the page content should be "The item with the requested ID was found"
