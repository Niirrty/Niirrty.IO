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
use Niirrty\IO\FileFormatException;
use Niirrty\IO\FileNotFoundException;
use Niirrty\IO\IOException;
use Niirrty\NiirrtyException;
use Niirrty\PhpException;
use PHPUnit\Framework\TestCase;


/**
 * Test case for the class `Niirrty\IO\FileFormatException`.
 *
 * Findings see `journal/04-file-format-exception.md`: the source is NOT changed,
 * the tests assert the CURRENT behaviour. All optional parameters of
 * `__construct()` are explicitly nullable (`?string $message`, `?\Throwable
 * $previous`) so - in contrast to `FileAccessException` - the FIRST use of the
 * class definition does NOT raise a deprecation notice and works as usual.
 * Nevertheless this test must stay the first test method to document that.
 *
 * @since v0.1.0
 */
class FileFormatExceptionTest extends TestCase
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

        $ex = new FileFormatException( '/probe/first.txt' );

        $this->assertInstanceOf( FileFormatException::class, $ex );
        $this->assertSame( '/probe/first.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/first.txt]! File format is wrong or illegal.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );

    }

    /**
     * Test the class hierarchy: a `FileFormatException` is a
     * `Niirrty\IO\IOException`, a `Niirrty\NiirrtyException`, an `\Exception`
     * and a `\Throwable`, but not the other way round.
     *
     * @since v0.1.0
     */
    public function testClassHierarchy()
    {

        $ex = new FileFormatException( '/probe/file.txt' );

        $this->assertInstanceOf( FileFormatException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( $ex instanceof \Throwable );

        // The direct parent class is exactly `Niirrty\IO\IOException`.
        $this->assertSame( 'Niirrty\IO\IOException', (string) \get_parent_class( $ex ) );
        $this->assertSame( 'Niirrty\IO\FileFormatException', (string) \get_class( $ex ) );

        // An ordinary `IOException` is NO `FileFormatException`.
        $ioex = new IOException( '/probe/file.txt' );
        $this->assertNotInstanceOf( FileFormatException::class, $ioex );
        $this->assertSame( 'There was a error with path [/probe/file.txt]!', $ioex->getMessage() );

        // A `NiirrtyException` also is no `FileFormatException`.
        $nex = new NiirrtyException( 'some error', 512 );
        $this->assertNotInstanceOf( FileFormatException::class, $nex );
        $this->assertNotInstanceOf( IOException::class, $nex );

    }

    /**
     * Test the constructor with the file only: the file must be kept as path,
     * the own fixed text 'File format is wrong or illegal.' must be appended to
     * the `IOException` message - it is handed over as `$message` parameter to
     * the parent constructor - and the code must default to 256 (`\E_USER_ERROR`).
     *
     * @since v0.1.0
     */
    public function testConstructorDefaults()
    {

        $ex = new FileFormatException( '/probe/file.txt' );

        $this->assertSame( '/probe/file.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/probe/file.txt]! File format is wrong or illegal.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( \E_USER_ERROR, $ex->getCode() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // Each call returns a new, independent instance.
        $ex2 = new FileFormatException( '/probe/file.txt' );
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

        $ex = new FileFormatException( 'c:\\Temp\\My Folder\\äöü.txt' );

        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );
        // The path is used "as is" in the message too.
        $this->assertSame( 'There was a error with path [c:\\Temp\\My Folder\\äöü.txt]! File format is wrong or illegal.', $ex->getMessage() );

        // Calling `getPath()` again must be without side effects.
        $this->assertSame( 'c:\\Temp\\My Folder\\äöü.txt', $ex->getPath() );

        // An empty file parameter is allowed and stays empty.
        $ex = new FileFormatException( '' );
        $this->assertSame( '', $ex->getPath() );
        $this->assertSame( 'There was a error with path []! File format is wrong or illegal.', $ex->getMessage() );

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
        $ex = new FileFormatException( '/f.txt', null );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getMessage() );

        // An empty appendix behaves like no appendix.
        $ex = new FileFormatException( '/f.txt', '' );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getMessage() );

        // A real appendix is appended behind the own fixed text, separated by
        // a single space.
        $ex = new FileFormatException( '/f.txt', 'created by other process' );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal. created by other process', $ex->getMessage() );

        // A blank appendix is appended also, so the message ends with 2 spaces.
        $ex = new FileFormatException( '/f.txt', ' ' );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal.  ', $ex->getMessage() );

        // Findings: a "0" appendix is lost because `appendMessage()` tests the
        // appendix with `empty()` - but the own fixed text is NEVER lost.
        $ex = new FileFormatException( '/f.txt', '0' );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getMessage() );

        // The own message text is not configurable: a `$message` only ever adds
        // something, the text 'File format is wrong or illegal.' is always there.
        $this->assertStringContainsString( 'File format is wrong or illegal.', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter with `int` values: the code must be forwarded
     * to the inner `\Exception` and must not influence file or message.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeInt()
    {

        $ex = new FileFormatException( '/f.txt', null, 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getMessage() );

        $ex = new FileFormatException( '/f.txt', null, 0 );
        $this->assertSame( 0, $ex->getCode() );

        $ex = new FileFormatException( '/f.txt', null, \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

        $ex = new FileFormatException( '/other/file.txt', 'appendix', 512 );
        $this->assertSame( 512, $ex->getCode() );
        $this->assertSame( '/other/file.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/other/file.txt]! File format is wrong or illegal. appendix', $ex->getMessage() );

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

        new FileFormatException( '/f.txt', 'appendix', 'MY-CODE' );

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

        new FileFormatException( '/f.txt', 'appendix', '4711' );

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

        new FileFormatException( '/f.txt', 'appendix', 256.5 );

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

        new FileFormatException( null );

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

        new FileFormatException( '/f.txt', 4711 );

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
        $ex = new FileFormatException( '/f.txt' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A plain `\Exception` may be used as previous exception.
        $prev = new \Exception( 'prev msg' );
        $ex = new FileFormatException( '/f.txt', 'appendix', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );

        // A `NiirrtyException` may be used as previous exception too.
        $prev = new NiirrtyException( 'prev Niirrty msg', 512 );
        $ex = new FileFormatException( '/f.txt', 'appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( NiirrtyException::class, $ex->getPrevious() );
        $this->assertSame( 'prev Niirrty msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 512, $ex->getPrevious()->getCode() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal. appendix', $ex->getMessage() );

        // Another `FileFormatException` may be the previous exception.
        $prev = new FileFormatException( '/prev/file.txt' );
        $ex = new FileFormatException( '/f.txt', 'nested', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FileFormatException::class, $ex->getPrevious() );
        $this->assertSame( '/prev/file.txt', $ex->getPrevious()->getPath() );
        $this->assertSame( '/f.txt', $ex->getPath() );
        $this->assertSame( 'There was a error with path [/prev/file.txt]! File format is wrong or illegal.', $ex->getPrevious()->getMessage() );

        // Any other `\Throwable` is accepted as previous exception.
        $ex = new FileFormatException( '/f.txt', null, 256, new \RuntimeException( 'runtime' ) );
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

        new FileFormatException( '/f.txt', 'msg', 256, 'no throwable' );

    }

    /**
     * Test an `FileFormatException` in a `try`/`catch` block: neither the
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
            throw new FileFormatException( '/probe/broken.dat', 'header checksum mismatch' );
        }
        catch ( FileFormatException $ex )
        {
            $this->assertSame( '/probe/broken.dat', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/broken.dat]! File format is wrong or illegal. header checksum mismatch', $ex->getMessage() );
            $this->assertSame( 256, $ex->getCode() );
        }

        // Because it extends from `IOException` it may also be catched as one.
        try
        {
            throw new FileFormatException( '/probe/broken.dat' );
        }
        catch ( IOException $ex )
        {
            $this->assertInstanceOf( FileFormatException::class, $ex );
            $this->assertSame( '/probe/broken.dat', $ex->getPath() );
            $this->assertSame( 'There was a error with path [/probe/broken.dat]! File format is wrong or illegal.', $ex->getMessage() );
        }

        // And as a `NiirrtyException`.
        try
        {
            throw new FileFormatException( '/probe/broken.dat' );
        }
        catch ( NiirrtyException $ex )
        {
            $this->assertInstanceOf( FileFormatException::class, $ex );
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

        $ex = new FileFormatException( '/probe/file.txt' );
        $this->assertSame( 'ERROR(256): There was a error with path [/probe/file.txt]! File format is wrong or illegal.', $ex->getErrorMessage() );

        $ex = new FileFormatException( '/f.txt', null, \E_USER_WARNING );
        $this->assertSame( 'WARNING(512): There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getErrorMessage() );

        $ex = new FileFormatException( '/f.txt', null, \E_USER_DEPRECATED );
        $this->assertSame( 'DEPRECATED(16384): There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getErrorMessage() );

        $ex = new FileFormatException( '/f.txt', null, \E_USER_NOTICE );
        $this->assertSame( 'NOTICE(1024): There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getErrorMessage() );

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

        $ex = new FileFormatException( '/probe/file.txt', 'appendix', 4711 );

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

        $prev = new FileFormatException( '/prev/file.txt' );
        $ex = new FileFormatException( '/f.txt', 'nested', 256, $prev );

        // Default: separator is a single space.
        $this->assertSame( 'ERROR(256): There was a error with path [/f.txt]! File format is wrong or illegal. nested ERROR(256): There was a error with path [/prev/file.txt]! File format is wrong or illegal.', $ex->getErrorMessage() );

        // With new line separator.
        $expected = 'ERROR(256): There was a error with path [/f.txt]! File format is wrong or illegal. nested' . "\n" . 'ERROR(256): There was a error with path [/prev/file.txt]! File format is wrong or illegal.';
        $this->assertSame( $expected, $ex->getErrorMessage( true ) );

        // A non-Niirrty previous exception is included by its message only.
        $ex = new FileFormatException( '/f.txt', null, 256, new \Exception( 'plain previous' ) );
        $this->assertSame( 'ERROR(256): There was a error with path [/f.txt]! File format is wrong or illegal. plain previous', $ex->getErrorMessage() );

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

        $ex = new FileFormatException( '/probe/file.txt', 'appendix' );

        $str = (string) $ex;

        $this->assertStringContainsString( 'Niirrty\IO\FileFormatException', $str );
        $this->assertStringContainsString( 'There was a error with path [/probe/file.txt]! File format is wrong or illegal. appendix', $str );
        $this->assertStringNotContainsString( 'ERROR(256)', $str );

        // An own `__toString()` would be required to change that: the message
        // alone is identical to `getMessage()` of the instance.
        $this->assertStringContainsString( $ex->getMessage(), $str );

    }

    /**
     * Test the inherited `__toString()` method with a previous exception: a
     * previous `Niirrty` exception is appended by its own `toCustomString()`
     * result, indented by one indent level (default 3 spaces), so the previous
     * exception's class name and message are part of the string too.
     *
     * @since v0.1.0
     */
    public function testToStringWithPrevious()
    {

        $prev = new FileFormatException( '/prev/file.txt' );
        $ex = new FileFormatException( '/f.txt', 'nested', 256, $prev );

        $str = (string) $ex;

        $this->assertStringContainsString( 'Niirrty\IO\FileFormatException', $str );
        $this->assertStringContainsString( $ex->getMessage(), $str );

        // The previous exception is appended in a new, indented line.
        $this->assertStringContainsString( "\n   Niirrty\\IO\\FileFormatException", $str );
        $this->assertStringContainsString( 'There was a error with path [/prev/file.txt]! File format is wrong or illegal.', $str );

        // Still no `getErrorMessage()` code name (also not for the previous
        // exception).
        $this->assertStringNotContainsString( 'ERROR(256)', $str );

    }

    /**
     * Test the inherited `__toString()` method with a previous exception, that
     * is NOT a `NiirrtyException`: `toCustomString()` also evaluates
     * `GetCodeName()` for the previous exception, so a previous exception with
     * an unusable error level (a plain `\Exception`'s default code `0`) throws
     * a `Niirrty\PhpException` - even when `getErrorMessage()` works fine for
     * the very same instance (findings see the journal!).
     *
     * @since v0.1.0
     */
    public function testToStringWithPreviousNotUsable()
    {

        $ex = new FileFormatException( '/f.txt', 'app', 256, new \Exception( 'plain previous' ) );

        // `getErrorMessage()` only uses the previous exception's message so it
        // neither throws nor evaluates the previous exception's code.
        $this->assertSame( 'ERROR(256): There was a error with path [/f.txt]! File format is wrong or illegal. app plain previous', $ex->getErrorMessage() );

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Constant E_STRICT is deprecated since 8.4, the error level was removed' );

        (string) $ex;

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

        $rc = new \ReflectionClass( FileFormatException::class );

        $this->assertSame( 'Niirrty\IO\FileFormatException', $rc->getName() );
        $this->assertSame( 'Niirrty\IO\IOException', $rc->getParentClass()->getName() );
        $this->assertFalse( $rc->isAbstract() );
        $this->assertSame( [], $rc->getConstants() );

        $rm = new \ReflectionMethod( FileFormatException::class, '__construct' );
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

        // The default code value of the own signature is the constant `\E_USER_ERROR`,
        // so `getDefaultValue()` returns the resolved value 256 and
        // `getDefaultValueConstantName()` the constant itself.
        $this->assertFalse( $ps[0]->isDefaultValueAvailable() );
        $this->assertTrue( $ps[1]->isDefaultValueAvailable() );
        $this->assertTrue( $ps[2]->isDefaultValueAvailable() );
        $this->assertTrue( $ps[3]->isDefaultValueAvailable() );
        $this->assertTrue( $ps[2]->isDefaultValueConstant() );
        $this->assertSame( 'E_USER_ERROR', $ps[2]->getDefaultValueConstantName() );
        $this->assertSame( 256, $ps[2]->getDefaultValue() );
        $this->assertNull( $ps[1]->getDefaultValue() );
        $this->assertNull( $ps[3]->getDefaultValue() );
        $this->assertSame( 'string', (string) $ps[0]->getType() );
        $this->assertSame( '?string', (string) $ps[1]->getType() );
        $this->assertSame( 'string|int', (string) $ps[2]->getType() );
        $this->assertSame( '?Throwable', (string) $ps[3]->getType() );

        // The own constructor of the class is the declared one (not an inherited
        // method of `Niirrty\IO\IOException`).
        $this->assertSame( 'Niirrty\IO\FileFormatException', $rc->getMethod( '__construct' )->getDeclaringClass()->getName() );
        $this->assertSame( 'Niirrty\IO\IOException', $rc->getMethod( 'getPath' )->getDeclaringClass()->getName() );

    }

    /**
     * Test the reflection of the class in combination with the methods filter:
     * in the CURRENT PHP version (8.5.12) the first parameter of
     * `ReflectionClass::getMethods()` is no more the boolean
     * `includeInherited` but an `?int` filter bitmask, so a boolean value is
     * rejected in a file with `strict_types` and the own methods only are
     * reachable by an own filter value (findings see the journal!).
     *
     * @since v0.1.0
     */
    public function testReflectionMethodsFilter()
    {

        $rc = new \ReflectionClass( FileFormatException::class );

        // Without filter parameter the inherited methods are included too, so
        // the own `__construct()` can NOT be counted this way.
        $this->assertGreaterThan( 1, \count( $rc->getMethods() ) );

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'ReflectionClass::getMethods(): Argument #1 ($filter) must be of type ?int, false given' );

        $rc->getMethods( false );

    }

    /**
     * Test that a `FileFormatException` is NOT one of the sibling exception
     * classes of the `Niirrty\IO` package (and vice versa), even if their
     * messages only differ in the fixed, own text.
     *
     * @since v0.1.0
     */
    public function testNotInstanceOfSiblings()
    {

        $ex = new FileFormatException( '/f.txt' );
        $sibling = new FileNotFoundException( '/f.txt' );
        $sibling2 = new FileAlreadyExistsException( '/f.txt' );

        $this->assertNotInstanceOf( FileNotFoundException::class, $ex );
        $this->assertNotInstanceOf( FileAlreadyExistsException::class, $ex );
        $this->assertNotInstanceOf( FileFormatException::class, $sibling );
        $this->assertNotInstanceOf( FileFormatException::class, $sibling2 );

        // All are `IOException`s, but they keep their own message text.
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( IOException::class, $sibling );
        $this->assertInstanceOf( IOException::class, $sibling2 );
        $this->assertSame( 'There was a error with path [/f.txt]! File format is wrong or illegal.', $ex->getMessage() );
        $this->assertSame( 'There was a error with path [/f.txt]! File not exists.', $sibling->getMessage() );
        $this->assertSame( 'There was a error with path [/f.txt]! The File does already exist.', $sibling2->getMessage() );

        // All share the same, inherited `getPath()` behaviour.
        $this->assertSame( '/f.txt', $ex->getPath() );
        $this->assertSame( '/f.txt', $sibling->getPath() );
        $this->assertSame( '/f.txt', $sibling2->getPath() );

    }

    #endregion


}
