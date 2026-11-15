<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license    MIT
 * @package    Niirrty\Tests\IO
 * @since      2026-11-15
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Tests\IO;


use Niirrty\IO\FileAlreadyExistsException;
use Niirrty\IO\FileNotFoundException;
use Niirrty\IO\IOException;
use Niirrty\NiirrtyException;
use Niirrty\PhpException;
use PHPUnit\Framework\TestCase;


/**
 * Test case for the class `Niirrty\IO\FileAlreadyExistsException`.
 *
 * Findings see `journal/03-file-already-exists-exception.md`: the source is NOT
 * changed, the tests assert the CURRENT behaviour. All optional parameters of
 * `__construct()` are explicitly nullable (`?string $message`, `?\Throwable
 * $previous`) so - in contrast to `FileAccessException` - the FIRST use of the
 * class definition does NOT raise a deprecation notice and works as usual.
 * Nevertheless this test must stay the first test method to document that.
 *
 * @since v0.1.0
 */
class FileAlreadyExistsExceptionTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test the FIRST use of the class definition: unlike `FileAccessException`
     * this class declares no soft-nullable parameter, so no PHP 8.5 deprecation
     * notice is emitted at the class definition and - as long as this test
     * class uses the class first - no `Niirrty\PhpException` is thrown.
     *
     * This test must stay the FIRST test method to document that behaviour.
     *
     * @since v0.1.0
     */
    public function testFirstUseOfClassDefinition()
    {

        $ex = new FileAlreadyExistsException( '/probe/first.txt' );

        $this->assertInstanceOf( FileAlreadyExistsException::class, $ex );
        $this->assertSame( '/probe/first.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/first.txt]! The File does already exist.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );

    }

    /**
     * Test the class hierarchy: a `FileAlreadyExistsException` is a
     * `Niirrty\IO\IOException`, a `Niirrty\NiirrtyException`, an `\Exception`
     * and a `\Throwable`, but not the other way round.
     *
     * @since v0.1.0
     */
    public function testClassHierarchy()
    {

        $ex = new FileAlreadyExistsException( '/probe/file.txt' );

        $this->assertInstanceOf( FileAlreadyExistsException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( $ex instanceof \Throwable );

        // The direct parent class is exactly `Niirrty\IO\IOException`.
        $this->assertSame( 'Niirrty\IO\IOException', (string) \get_parent_class( $ex ) );
        $this->assertSame( 'Niirrty\IO\FileAlreadyExistsException', (string) \get_class( $ex ) );

        // An ordinary `IOException` is NO `FileAlreadyExistsException`.
        $ioex = new IOException( '/probe/file.txt' );
        $this->assertNotInstanceOf( FileAlreadyExistsException::class, $ioex );
        $this->assertSame( 'There was a error with path [/probe/file.txt]!', $ioex->getMessage() );

        // A `NiirrtyException` also is no `FileAlreadyExistsException`.
        $nex = new NiirrtyException( 'some error', 512 );
        $this->assertNotInstanceOf( FileAlreadyExistsException::class, $nex );
        $this->assertNotInstanceOf( IOException::class, $nex );

    }

    /**
     * Test the constructor with the file only: the file must be kept as path,
     * the own fixed text 'The File does already exist.' must be appended to the
     * `IOException` message - it is handed over as `$message` parameter to the
     * parent constructor - and the code must default to 256 (`\E_USER_ERROR`).
     *
     * @since v0.1.0
     */
    public function testConstructorDefaults()
    {

        $ex = new FileAlreadyExistsException( '/probe/file.txt' );

        $this->assertSame( '/probe/file.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/file.txt]! The File does already exist.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( \E_USER_ERROR, $ex->getCode() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // Each call returns a new, independent instance.
        $ex2 = new FileAlreadyExistsException( '/probe/file.txt' );
        $this->assertNotEquals( $ex, $ex2 );

    }

    /**
     * Test the `$file` parameter and `getPath()`: the file must always be
     * returned "as is", also one with spaces, umlauts and backslashes, and an
     * empty file is accepted. `getPath()` itself is inherited from
     * `Niirrty\IO\IOException`.
     *
     * @since v0.1.0
     */
    public function testGetPath()
    {

        $ex = new FileAlreadyExistsException( 'c:\\Temp\\My Folder\\äöü.txt' );

        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );
        // The path is used "as is" in the message too.
        $this->assertSame( 'There was a error with path [c:\\Temp\\My Folder\\äöü.txt]! The File does already exist.', $ex->getMessage() );

        // Calling `getPath()` again must be without side effects.
        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );

        // An empty file parameter is allowed and stays empty.
        $ex = new FileAlreadyExistsException( '' );
        $this->assertSame( '', $ex->getPath() );
        $this->assertSame( 'There was a error with path []! The File does already exist.', $ex->getMessage() );

    }

    /**
     * Test the `$message` parameter, which is appended to the own fixed text.
     * `NiirrtyException::appendMessage()` decides with `empty()` whether
     * anything is appended.
     *
     * @since v0.1.0
     */
    public function testConstructorMessage()
    {

        // NULL appendix behaves like no appendix at all.
        $ex = new FileAlreadyExistsException( '/f.txt', null );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.', $ex->getMessage() );

        // An empty appendix behaves like no appendix.
        $ex = new FileAlreadyExistsException( '/f.txt', '' );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.', $ex->getMessage() );

        // A real appendix is appended behind the own fixed text, separated by
        // a single space.
        $ex = new FileAlreadyExistsException( '/f.txt', 'created by other process' );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist. created by other process', $ex->getMessage() );

        // A blank appendix is appended also, so the message ends with 2 spaces.
        $ex = new FileAlreadyExistsException( '/f.txt', ' ' );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.  ', $ex->getMessage() );

        // Findings: a "0" appendix is lost because `appendMessage()` tests the
        // appendix with `empty()` - but the own fixed text is NEVER lost.
        $ex = new FileAlreadyExistsException( '/f.txt', '0' );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.', $ex->getMessage() );

        // The own message text is not configurable: a `$message` only ever adds
        // something, the text 'The File does already exist.' is always there.
        $this->assertStringContainsString( 'The File does already exist.', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter with `int` values: the code must be forwarded
     * to the inner `\Exception` and must not influence file or message.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeInt()
    {

        $ex = new FileAlreadyExistsException( '/f.txt', null, 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.', $ex->getMessage() );

        $ex = new FileAlreadyExistsException( '/f.txt', null, 0 );
        $this->assertSame( 0, $ex->getCode() );

        $ex = new FileAlreadyExistsException( '/f.txt', null, \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

        $ex = new FileAlreadyExistsException( '/other/file.txt', 'appendix', 512 );
        $this->assertSame( 512, $ex->getCode() );
        $this->assertSame( '/other/file.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/other/file.txt]! The File does already exist. appendix', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter with an alphanumeric string: the signature
     * documents `string|int`, so the call itself is valid, but the inner
     * `\Exception` constructor rejects the code with a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FileAlreadyExistsException( '/f.txt', 'appendix', 'MY-CODE' );

    }

    /**
     * Test the `$code` parameter with a numeric string: also these are rejected
     * by the inner `\Exception` constructor (see findings in the journal!).
     *
     * @since v0.1.0
     */
    public function testConstructorCodeNumericString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FileAlreadyExistsException( '/f.txt', 'appendix', '4711' );

    }

    /**
     * Test the `$code` parameter with a float: the parameter type `string|int`
     * rejects it directly at the constructor call, so - in contrast to
     * `Niirrty\IO\IOException` - a `TypeError` and not a `Niirrty\PhpException`
     * is thrown (findings see the journal!).
     *
     * @since v0.1.0
     */
    public function testConstructorCodeFloat()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #3 ($code) must be of type string|int, float given' );

        new FileAlreadyExistsException( '/f.txt', 'appendix', 256.5 );

    }

    /**
     * Test the constructor with `NULL` as `$file`: the parameter is declared
     * `string` without default so `NULL` is rejected with a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testConstructorFileNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($file) must be of type string, null given' );

        new FileAlreadyExistsException( null );

    }

    /**
     * Test the `$message` parameter with a not compatible type: it is declared
     * `?string` so an `int` is rejected with a `TypeError` (strict typing).
     *
     * @since v0.1.0
     */
    public function testConstructorMessageWrongType()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #2 ($message) must be of type ?string, int given' );

        new FileAlreadyExistsException( '/f.txt', 4711 );

    }

    /**
     * Test the `$previous` parameter: any `\Throwable` may be used, and the
     * previous exception has nothing to do with own file, message or code.
     *
     * @since v0.1.0
     */
    public function testConstructorPrevious()
    {

        // Without previous exception.
        $ex = new FileAlreadyExistsException( '/f.txt' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A plain `\Exception` may be used as previous exception.
        $prev = new \Exception( 'prev msg' );
        $ex = new FileAlreadyExistsException( '/f.txt', 'appendix', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );

        // A `NiirrtyException` may be used as previous exception too.
        $prev = new NiirrtyException( 'prev Niirrty msg', 512 );
        $ex = new FileAlreadyExistsException( '/f.txt', 'appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( NiirrtyException::class, $ex->getPrevious() );
        $this->assertSame( 'prev Niirrty msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 512, $ex->getPrevious()->getCode() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist. appendix', $ex->getMessage() );

        // Another `FileAlreadyExistsException` may be the previous exception.
        $prev = new FileAlreadyExistsException( '/prev/file.txt' );
        $ex = new FileAlreadyExistsException( '/f.txt', 'nested', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FileAlreadyExistsException::class, $ex->getPrevious() );
        $this->assertSame( '/prev/file.txt', $ex->getPrevious()->getPath() );
        $this->assertSame( '/f.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/prev/file.txt]! The File does already exist.', $ex->getPrevious()->getMessage() );

        // Any other `\Throwable` is accepted as previous exception.
        $ex = new FileAlreadyExistsException( '/f.txt', null, 256, new \RuntimeException( 'runtime' ) );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( \RuntimeException::class, $ex->getPrevious() );

    }

    /**
     * Test the `$previous` parameter with a value, that is no `\Throwable`: the
     * parameter type `?\Throwable` rejects it directly at the constructor call.
     *
     * @since v0.1.0
     */
    public function testConstructorPreviousWrongType()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #4 ($previous) must be of type ?Throwable, string given' );

        new FileAlreadyExistsException( '/f.txt', 'msg', 256, 'no throwable' );

    }

    /**
     * Test an `FileAlreadyExistsException` in a `try`/`catch` block: neither the
     * file, the message nor the code may change when throwing. Because it
     * extends from `IOException` and `NiirrtyException` it may be catched as
     * one of them too.
     *
     * @since v0.1.0
     */
    public function testThrowAndCatch()
    {

        try
        {
            throw new FileAlreadyExistsException( '/probe/exists.txt', 'file is not empty' );
        }
        catch ( FileAlreadyExistsException $ex )
        {
            $this->assertSame( '/probe/exists.txt', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/exists.txt]! The File does already exist. file is not empty', $ex->getMessage() );
            $this->assertSame( 256, $ex->getCode() );
        }

        // Because it extends from `IOException` it may also be catched as one.
        try
        {
            throw new FileAlreadyExistsException( '/probe/exists.txt' );
        }
        catch ( IOException $ex )
        {
            $this->assertInstanceOf( FileAlreadyExistsException::class, $ex );
            $this->assertSame( '/probe/exists.txt', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/exists.txt]! The File does already exist.', $ex->getMessage() );
        }

        // And as a `NiirrtyException`.
        try
        {
            throw new FileAlreadyExistsException( '/probe/exists.txt' );
        }
        catch ( NiirrtyException $ex )
        {
            $this->assertInstanceOf( FileAlreadyExistsException::class, $ex );
            $this->assertTrue( $ex instanceof \Exception );
        }

    }

    /**
     * Test the inherited `getErrorMessage()` method for all error levels, that
     * `NiirrtyException::GetCodeName()` handles before the deprecated constant
     * `E_STRICT` is evaluated.
     *
     * @since v0.1.0
     */
    public function testInheritedGetErrorMessage()
    {

        $ex = new FileAlreadyExistsException( '/probe/file.txt' );
        $this->assertSame( 'ERROR(256): There was a error with path [/probe/file.txt]! The File does already exist.', $ex->getErrorMessage() );

        $ex = new FileAlreadyExistsException( '/f.txt', null, \E_USER_WARNING );
        $this->assertSame( 'WARNING(512): There was a error with path [/f.txt]! The File does already exist.', $ex->getErrorMessage() );

        $ex = new FileAlreadyExistsException( '/f.txt', null, \E_USER_DEPRECATED );
        $this->assertSame( 'DEPRECATED(16384): There was a error with path [/f.txt]! The File does already exist.', $ex->getErrorMessage() );

        $ex = new FileAlreadyExistsException( '/f.txt', null, \E_USER_NOTICE );
        $this->assertSame( 'NOTICE(1024): There was a error with path [/f.txt]! The File does already exist.', $ex->getErrorMessage() );

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

        $ex = new FileAlreadyExistsException( '/probe/file.txt', 'appendix', 4711 );

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Constant E_STRICT is deprecated since 8.4, the error level was removed' );

        $ex->getErrorMessage();

    }

    /**
     * Test the inherited `getErrorMessage()` method with a previous exception:
     * the `$appendPreviousByNewline` parameter decides the separator, and the
     * previous exception is appended behind the own message. The previous
     * exception's code must also be a usable error level!
     *
     * @since v0.1.0
     */
    public function testGetErrorMessageWithPrevious()
    {

        $prev = new FileAlreadyExistsException( '/prev/file.txt' );
        $ex = new FileAlreadyExistsException( '/f.txt', 'nested', 256, $prev );

        // Default: separator is a single space.
        $this->assertSame( 'ERROR(256): There was a error with path [/f.txt]! The File does already exist. nested ERROR(256): There was a error with path [/prev/file.txt]! The File does already exist.', $ex->getErrorMessage() );

        // With new line separator.
        $expected = 'ERROR(256): There was a error with path [/f.txt]! The File does already exist. nested' . "\n" . 'ERROR(256): There was a error with path [/prev/file.txt]! The File does already exist.';
        $this->assertSame( $expected, $ex->getErrorMessage( true ) );

        // A non-Niirrty previous exception is included by its message only.
        $ex = new FileAlreadyExistsException( '/f.txt', null, 256, new \Exception( 'plain previous' ) );
        $this->assertSame( 'ERROR(256): There was a error with path [/f.txt]! The File does already exist. plain previous', $ex->getErrorMessage() );

    }

    /**
     * Test the inherited `__toString()` method: casting the exception to a
     * string must return the `toCustomString()` result, which contains the
     * fully qualified class name, the location and the message - but NOT the
     * code name of `getErrorMessage()`.
     *
     * @since v0.1.0
     */
    public function testToString()
    {

        $ex = new FileAlreadyExistsException( '/probe/file.txt', 'appendix' );

        $str = (string) $ex;

        $this->assertStringContainsString( 'Niirrty\IO\FileAlreadyExistsException', $str );
        $this->assertStringContainsString( 'There was a error with path [/probe/file.txt]! The File does already exist. appendix', $str );
        $this->assertStringNotContainsString( 'ERROR(256)', $str );

        // An own `__toString()` would be required to change that: the message
        // alone is identical to `getMessage()` of the instance.
        $this->assertStringContainsString( $ex->getMessage(), $str );

    }

    /**
     * Test the class structure by reflection: the class declares exactly one
     * own method - `__construct()` - with the four documented parameters, it
     * extends from `Niirrty\IO\IOException` and declares no own constants.
     *
     * @since v0.1.0
     */
    public function testReflection()
    {

        $rc = new \ReflectionClass( FileAlreadyExistsException::class );

        $this->assertSame( 'Niirrty\IO\FileAlreadyExistsException', $rc->getName() );
        $this->assertSame( 'Niirrty\IO\IOException', $rc->getParentClass()->getName() );
        $this->assertFalse( $rc->isAbstract() );
        $this->assertSame( [], $rc->getConstants() );

        $rm = new \ReflectionMethod( FileAlreadyExistsException::class, '__construct' );
        $ps = $rm->getParameters();

        $this->assertSame( 4, \count( $ps ) );
        $this->assertSame( 'file', $ps[0]->getName() );
        $this->assertSame( 'message', $ps[1]->getName() );
        $this->assertSame( 'code', $ps[2]->getName() );
        $this->assertSame( 'previous', $ps[3]->getName() );

        $this->assertFalse( $ps[0]->isOptional() );
        $this->assertTrue( $ps[1]->isOptional() );
        $this->assertTrue( $ps[2]->isOptional() );
        $this->assertTrue( $ps[3]->isOptional() );

        // In contrast to the promoted `$path` of `Niirrty\IO\IOException` the
        // `$file` parameter is NO promoted property.
        $this->assertFalse( $ps[0]->isPromoted() );

    }

    /**
     * Test that a `FileAlreadyExistsException` is NOT one of the sibling
     * exception classes of the `Niirrty\IO` package (and vice versa), even if
     * their messages only differ in the fixed, own text.
     *
     * @since v0.1.0
     */
    public function testNotInstanceOfSiblings()
    {

        $ex = new FileAlreadyExistsException( '/f.txt' );
        $sibling = new FileNotFoundException( '/f.txt' );

        $this->assertNotInstanceOf( FileNotFoundException::class, $ex );
        $this->assertNotInstanceOf( FileAlreadyExistsException::class, $sibling );

        // Both are `IOException`s, but they keep their own message text.
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( IOException::class, $sibling );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.', $ex->getMessage() );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.', $sibling->getMessage() );

        // Both share the same, inherited `getPath()` behaviour.
        $this->assertSame( '/f.txt', $ex->getPath() );
        $this->assertSame( '/f.txt', $sibling->getPath() );

    }

    #endregion


}
