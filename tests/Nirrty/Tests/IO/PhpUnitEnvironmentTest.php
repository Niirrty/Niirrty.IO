<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2026, Niirrty
 * @package    Niirrty\Tests\IO
 * @since      2026-11-15
 * @version    0.6.1
 */


namespace Niirrty\Tests\IO;


use PHPUnit\Framework\TestCase;


/**
 * This is no real test: it is just a placeholder to prove that the PHPUnit
 * test environment (phpunit.xml.dist + tests/boot.php + the composer
 * dependencies) is up and running.
 *
 * @since v0.6.1
 */
class PhpUnitEnvironmentTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test if the test environment itself is up and running.
     *
     * @since v0.6.1
     */
    public function testEnvironment()
    {

        $this->assertTrue( true );
        // Proves that the autoload chain - including "src/required-code.php" - works.
        $this->assertTrue( \defined( 'IS_WIN' ) );

    }

    #endregion


}
