<?php
/**
 * @author         Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license        MIT
 * @package        Niirrty\Tests\IO
 * @since          2026-11-15
 * @version        0.1.0
 */


namespace Niirrty\Tests\IO;


use Niirrty\IO\IOException;
use Niirrty\NiirrtyException;
use Niirrty\PhpException;
use PHPUnit\Framework\TestCase;


/**
 * Test case for the class `Niirrty\IO\IOException`.
 *
 * @since v0.1.0
 */
class IOExceptionTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test the class hierarchy: an `IOException` is a `Niirrty\NiirrtyException`,
     * a `\Exception` and a `\Throwable`.
     *
     * @since v0.1.0
     */
    public function testClassHierarchy()
    {

        $ex = new IOException( 'some/path.txt' );

        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertInstanceOf( \Throwable::class, $ex );

    }

    /**
     * Test the constructor with the path only: the path must be stored, the
     * default message must be built and the default code must be `256`
     * (`\E_USER_ERROR`).
     *
     * @since v0.1.0
     */
    public function testConstructorDefaults()
    {

        $ex = new IOException( 'some/path.txt' );

        $this->assertSame( 'some/path.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [some/path.txt]!', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

    }

    /**
     * Test `getPath()`: the path must always be returned "as is", also one
     * with spaces, umlauts and backslashes.
     *
     * @since v0.1.0
     */
    public function testGetPath()
    {

        $ex = new IOException( 'c:\\Temp\\My Folder\\äöü.txt' );

        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );
        // The path is used "as is" in the message too.
        $this->assertSame( 'There was a error with path [c:\\Temp\\My Folder\\äöü.txt]!', $ex->getMessage() );

        // Calling `getPath()` again must be without side effects.
        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );

        // An empty path is allowed and stays empty.
        $ex = new IOException( '' );
        $this->assertSame( '', $ex->getPath() );
        $this->assertSame( 'There was a error with path []!', $ex->getMessage() );

    }

    /**
     * Test how the error message is built, especially the appendix (`$message`).
     *
     * @since v0.1.0
     */
    public function testConstructorMessage()
    {

        // NULL appendix behaves like no appendix at all.
        $ex = new IOException( 'p', null );
        $this->assertSame( 'There was a error with path [p]!', $ex->getMessage() );

        // An empty appendix behaves like no appendix.
        $ex = new IOException( 'p', '' );
        $this->assertSame( 'There was a error with path [p]!', $ex->getMessage() );

        // A real appendix is appended, separated by a single space.
        $ex = new IOException( 'p', 'some appendix' );
        $this->assertSame( 'There was a error with path [p]! some appendix', $ex->getMessage() );

        // A blank appendix is appended also, so the message ends with 2 spaces.
        $ex = new IOException( 'p', ' ' );
        $this->assertSame( 'There was a error with path [p]!  ', $ex->getMessage() );

        // Findings see `journal/01-ioexception.md`: a "0" appendix is lost because
        // `NiirrtyException::appendMessage()` tests the appendix with `empty()`.
        $ex = new IOException( 'p', '0' );
        $this->assertSame( 'There was a error with path [p]!', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter with `int` values.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeInt()
    {

        $ex = new IOException( 'p', null, 4711 );
        $this->assertSame( 4711, $ex->getCode() );

        $ex = new IOException( 'p', null, 0 );
        $this->assertSame( 0, $ex->getCode() );

        $ex = new IOException( 'p', null, \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

    }

    /**
     * Test the `$code` parameter with an alphanumeric string: the signature
     * allows `string|int` but on PHP 8.5 the inner `\Exception` constructor
     * rejects it with a `TypeError` (see findings in the journal!).
     *
     * @since v0.1.0
     */
    public function testConstructorCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int' );

        new IOException( 'p', null, 'MY-CODE' );

    }

    /**
     * Test the `$code` parameter with a numeric string: also these are rejected
     * on PHP 8.5 (see findings in the journal!).
     *
     * @since v0.1.0
     */
    public function testConstructorCodeNumericString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int' );

        new IOException( 'p', null, '4711' );

    }

    /**
     * Test the `$previous` parameter.
     *
     * @since v0.1.0
     */
    public function testConstructorPrevious()
    {

        // Without previous exception.
        $ex = new IOException( 'p' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A plain `\Exception` may be used as previous exception.
        $prev = new \Exception( 'prev msg' );
        $ex = new IOException( 'p', null, 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );

        // A `NiirrtyException` may be used as previous exception too.
        $prev = new NiirrtyException( 'prev Niirrty msg', 4712 );
        $ex = new IOException( 'p', 'appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( NiirrtyException::class, $ex->getPrevious() );
        $this->assertSame( 'prev Niirrty msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 4712, $ex->getPrevious()->getCode() );
        // The previous exception has nothing to do with the own message.
        $this->assertSame( 'There was a error with path [p]! appendix', $ex->getMessage() );

    }

    /**
     * Test an `IOException` in a `try`/`catch` block.
     *
     * @since v0.1.0
     */
    public function testThrowAndCatch()
    {

        try
        {
            throw new IOException( 'some/path.txt', 'thrown appendix' );
        }
        catch ( IOException $ex )
        {
            $this->assertSame( 'some/path.txt', $ex->getPath() );
            $this->assertSame( 'There was a error with path [some/path.txt]! thrown appendix', $ex->getMessage() );
            $this->assertSame( 256, $ex->getCode() );
        }

    }

    /**
     * Test the inherited `getErrorMessage()` method for a code, that is a
     * known PHP error level: it works and prepends the code name.
     *
     * @since v0.1.0
     */
    public function testGetErrorMessage()
    {

        $ex = new IOException( 'some/path.txt' );

        $this->assertSame( 'ERROR(256): There was a error with path [some/path.txt]!', $ex->getErrorMessage() );

    }

    /**
     * Test the inherited `getErrorMessage()` method for a code, that is NOT one
     * of the known PHP error levels (findings see `journal/01-ioexception.md`):
     * `NiirrtyException::GetCodeName()` internally evaluates the deprecated
     * constant `E_STRICT`, what Niirrty.Core's global error handler turns into a
     * thrown `Niirrty\PhpException`!
     *
     * @since v0.1.0
     */
    public function testGetErrorMessageNotUsable()
    {

        $ex = new IOException( 'some/path.txt', 'appendix', 4711 );

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Constant E_STRICT is deprecated since 8.4, the error level was removed' );

        $ex->getErrorMessage();

    }

    #endregion


}
