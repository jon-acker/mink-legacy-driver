<?php

use Behat\Behat\Tester\Exception\PendingException;
use Behat\Behat\Context\Context;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Mink\Session;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Routing\Route;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context
{
    /**
     * @var SessionBuilder
     */
    private $sessionBuilder;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var string
     */
    private $directory;

    /**
     * @BeforeScenario
     */
    public function prepareSessionBuilder()
    {
        $this->directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mink-legacy-driver' . DIRECTORY_SEPARATOR .
            md5(microtime() . rand(0, 10000));

        mkdir($this->directory, 0777, true);

        $this->sessionBuilder = new SessionBuilder($this->directory);
    }

    /**
     * Cleans test folders in the temporary directory.
     *
     * @BeforeSuite
     * @AfterSuite
     */
    public static function cleanTestFolders()
    {
        if (is_dir($dir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'mink-legacy-driver')) {
            $filesystem = new Filesystem();
            $filesystem->remove($dir);
        }
    }

    /**
     * @Given the :file file contains:
     */
    public function theFileContains($file, PyStringNode $content)
    {
        file_put_contents($this->directory . '/' . $file, $content->getRaw());
    }

    /**
     * @Given the file :file is configured as the unique frontend controller
     */
    public function theFileIsConfiguredAsTheUniqueFrontendController($file)
    {
        $this->addController($file, '/{catchall}', 'GET', 'catchall', '.*');
    }

    /**
     * @Given the file :file is configured as the frontend controller for :path
     */
    public function theFileIsConfiguredAsTheFrontendControllerFor($file, $path)
    {
        $this->addController($file, $path);
    }

    /**
     * @Given the file :file is configured as the frontend controller for :path and method :method
     */
    public function theFileIsConfiguredAsTheFrontendControllerForAndMethod($file, $path, $method)
    {
        $this->addController($file, $path, $method);
    }

    /**
     * @Given the file :file is configured as the frontend controller for :path with parameter :parameter allowed to be :value
     */
    public function theFileIsConfiguredAsTheFrontendControllerForWithParameterAllowedToBe($file, $path, $parameter, $value)
    {
        $this->addController($file, $path, 'GET', $parameter, $value);
    }

    /**
     * @param string      $file
     * @param string      $path
     * @param string      $method
     * @param string|null $parameter
     * @param string|null $value
     */
    private function addController($file, $path, $method = 'GET', $parameter = null, $value = null)
    {
        $requirements = array();
        if (isset($parameter)) {
            $requirements = array($parameter => $value);
        }

        $this->sessionBuilder->addController(new Route(
            $path,
            array('file' => $this->directory . '/' . $file),
            $requirements,
            array(),
            '',
            array(),
            array($method)
        ));
    }

    /**
     * @When /^I make a(?: "([^"]*)")? request to "([^"]*)"$/
     * @When /^I make a(?: "([^"]*)")? request to "([^"]*)" with no parameters$/
     */
    public function iMakeARequestTo($method, $path)
    {
        $this->session = $this->sessionBuilder->build();

        $this->session->getDriver()->getClient()->request(
            $method ?: 'GET',
            $path
        );
    }

    /**
     * @Then the page content should be :content
     */
    public function thePageContentShouldBe($content)
    {
        $pageContent = $this->session->getPage()->getContent();

        if ($pageContent !== $content) {
            throw new RuntimeException(sprintf(
                'Page content expected to be `%s` but `%s` was found.', $content, $pageContent
            ));
        }
    }

    /**
     * @Given the enviornment variable :variable is set to :value
     */
    public function theEnviornmentVariableIsSetTo($variable, $value)
    {
        $this->sessionBuilder->addEnvironmentVariable($variable, $value);
    }

    /**
     * @Given the file :file is configured to bootstrap with the application
     */
    public function theFileIsConfiguredToBootstrapWithTheApplication($file)
    {
        $this->sessionBuilder->addBootstrapScript($this->directory . '/' . $file);
    }

    /**
     * @Then the page content should include the error :errorMessage
     */
    public function thePageContentShouldIncludeTheError($errorMessage)
    {
        $pageContent = $this->session->getPage()->getContent();

        if (strpos($pageContent, $errorMessage) === false) {
            throw new RuntimeException(sprintf("Expected page contain error with: %s, page content was:\n%s", $errorMessage, $pageContent));
        }
    }
}
