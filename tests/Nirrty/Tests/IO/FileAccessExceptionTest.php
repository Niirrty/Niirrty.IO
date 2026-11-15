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


use Niirrty\IO\FileAccessException;
use Niirrty\IO\IOException;
use Niirrty\NiirrtyException;
use Niirrty\PhpException;
use PHPUnit\Framework\TestCase;


/**
 * Test case for the class `Niirrty\IO\FileAccessException`.
 *
 * Findings see `journal/02-file-access-exception.md`: the source is NOT changed,
 * the tests assert the CURRENT behaviour. Therefore the very first test method
 * must stay the first one, because the first use of the class definition raises
 * a `Niirrty\PhpException` (PHP 8.5 deprecation, converted by Niirrty.Core's
 * global error handler).
 *
 * @since v0.1.0
 */
class FileAccessExceptionTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test the CURRENT behaviour on the first use of the class definition:
     * `Read()` and `Write()` declare their `$message` parameter as `string` with
     * a `null` default value, what is implicitly nullable and therefore
     * deprecated on PHP 8.5. That deprecation notice is - like every notice -
     * turned into a thrown `Niirrty\PhpException` by Niirrty.Core's global
     * error handler.
     *
     * This test must stay the FIRST test method because that notice is thrown
     * only once per process, all following tests use the class as usual.
     *
     * @since v0.1.0
     */
    public function testImplicitNullableParameter()
    {

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Implicitly marking parameter $message as nullable is deprecated' );

        new FileAccessException( '/probe/file.txt' );

    }

    /**
     * Test all `ACCESS_*` class constants: their names must exist and their
     * values must be exactly as declared in the source.
     *
     * @since v0.1.0
     */
    public function testClassConstants()
    {

        // The constant names exist...
        $this->assertTrue( \defined( '\Niirrty\IO\FileAccessException::ACCESS_READ' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FileAccessException::ACCESS_WRITE' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FileAccessException::ACCESS_READWRITE' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FileAccessException::ACCESS_CREATE' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FileAccessException::ACCESS_DELETE' ) );
        // ...and unknown ones don't.
        $this->assertFalse( \defined( '\Niirrty\IO\FileAccessException::ACCESS_UNKNOWN' ) );

        // The constant values.
        $this->assertSame( 'read', FileAccessException::ACCESS_READ );
        $this->assertSame( 'write', FileAccessException::ACCESS_WRITE );
        $this->assertSame( 'read and write', FileAccessException::ACCESS_READWRITE );
        $this->assertSame( 'create', FileAccessException::ACCESS_CREATE );
        $this->assertSame( 'delete', FileAccessException::ACCESS_DELETE );

        // The constants are used "case sensitively" and are no numbers.
        $this->assertNotSame( 'READ', FileAccessException::ACCESS_READ );

    }

    /**
     * Test the constructor with the file only: the access type must default to
     * `ACCESS_READ`, the message must be built and the error code must default
     * to 256 (`\E_USER_ERROR`).
     *
     * @since v0.1.0
     */
    public function testConstructorDefaults()
    {

        $ex = new FileAccessException( '/probe/file.txt' );

        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );
        // The message from the source ("Could not %s file.") is appended by the
        // `IOException` message, which was build from the file.
        $this->assertSame( 'There was a error with path [/probe/file.txt]! Could not read file.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        // The file is forwarded to the `IOException` and stays readable.
        $this->assertSame( '/probe/file.txt', $ex->getPath() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

    }

    /**
     * Test the constructor with every `ACCESS_*` constant given explicit: for
     * each one the access type must be kept and the message must be
     * "Could not <access> file.".
     *
     * @since v0.1.0
     */
    public function testConstructorAccessType()
    {

        $ex = new FileAccessException( '/f.txt', FileAccessException::ACCESS_READ );
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read file.', $ex->getMessage() );

        $ex = new FileAccessException( '/f.txt', FileAccessException::ACCESS_WRITE );
        $this->assertSame( FileAccessException::ACCESS_WRITE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file.', $ex->getMessage() );

        $ex = new FileAccessException( '/f.txt', FileAccessException::ACCESS_READWRITE );
        $this->assertSame( FileAccessException::ACCESS_READWRITE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read and write file.', $ex->getMessage() );

        $ex = new FileAccessException( '/f.txt', FileAccessException::ACCESS_CREATE );
        $this->assertSame( FileAccessException::ACCESS_CREATE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not create file.', $ex->getMessage() );

        $ex = new FileAccessException( '/f.txt', FileAccessException::ACCESS_DELETE );
        $this->assertSame( FileAccessException::ACCESS_DELETE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not delete file.', $ex->getMessage() );

    }

    /**
     * Test the constructor with an access type, that is none of the `ACCESS_*`
     * constants: the value is NOT validated, it is simply used in the message.
     *
     * @since v0.1.0
     */
    public function testConstructorAccessFreeValue()
    {

        $ex = new FileAccessException( '/f.txt', 'flush' );

        $this->assertSame( 'flush', $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not flush file.', $ex->getMessage() );

        // Also an empty access type is accepted.
        $ex = new FileAccessException( '/f.txt', '' );
        $this->assertSame( '', $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not  file.', $ex->getMessage() );

    }

    /**
     * Test the constructor with `NULL` as access type: although the parameter
     * has `NULL` as default value, its type is `string` and - different to
     * ordinary parameters - promoted property parameters may NOT be implicitly
     * nullable so passing `NULL` is a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testConstructorAccessNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #2 ($access) must be of type string, null given' );

        new FileAccessException( '/f.txt', null );

    }

    /**
     * Test how the optional error message is appended to the message that was
     * built from the access type. `NiirrtyException::appendMessage()` decides
     * with `empty()` wether to append something or not.
     *
     * @since v0.1.0
     */
    public function testConstructorMessageAppendix()
    {

        // NULL appendix behaves like no appendix at all.
        $ex = new FileAccessException( '/f.txt', 'write', null );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file.', $ex->getMessage() );

        // An empty appendix behaves like no appendix.
        $ex = new FileAccessException( '/f.txt', 'write', '' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file.', $ex->getMessage() );

        // A real appendix is appended, separated by a single space.
        $ex = new FileAccessException( '/f.txt', 'write', 'disk full' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file. disk full', $ex->getMessage() );

        // A blank appendix is appended also, so the message ends with 2 spaces.
        $ex = new FileAccessException( '/f.txt', 'write', ' ' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file.  ', $ex->getMessage() );

        // Findings: a "0" appendix is lost because `appendMessage()` tests the
        // appendix with `empty()`.
        $ex = new FileAccessException( '/f.txt', 'write', '0' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file.', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter with `int` values.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeInt()
    {

        $ex = new FileAccessException( '/f.txt', 'read', null, 4711 );
        $this->assertSame( 4711, $ex->getCode() );

        $ex = new FileAccessException( '/f.txt', 'read', null, 0 );
        $this->assertSame( 0, $ex->getCode() );

        $ex = new FileAccessException( '/f.txt', 'read', null, \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

        // The code has nothing to do with the access type.
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );

    }

    /**
     * Test the `$code` parameter with an alphanumeric string: the signature
     * documents `string|int` but the inner `\Exception` constructor rejects it.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FileAccessException( '/f.txt', 'read', 'appendix', 'MY-CODE' );

    }

    /**
     * Test the `$code` parameter with a numeric string: also these are rejected
     * by the inner `\Exception` constructor (findings see the journal).
     *
     * @since v0.1.0
     */
    public function testConstructorCodeNumericString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FileAccessException( '/f.txt', 'read', 'appendix', '4711' );

    }

    /**
     * Test `getAccessType()` for instances, that where built with the
     * constructor as well as for the ones from the factory methods.
     *
     * @since v0.1.0
     */
    public function testGetAccessType()
    {

        $ex = new FileAccessException( '/f.txt' );
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );
        // Calling it more then once must be without side effects.
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );

        $ex = new FileAccessException( '/f.txt', FileAccessException::ACCESS_DELETE );
        $this->assertSame( FileAccessException::ACCESS_DELETE, $ex->getAccessType() );

        $ex = FileAccessException::Read( '/f.txt' );
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );

        $ex = FileAccessException::Write( '/f.txt' );
        $this->assertSame( FileAccessException::ACCESS_WRITE, $ex->getAccessType() );

    }

    /**
     * Test the static factory method `Read()`: it must build a new instance
     * with the access type `ACCESS_READ` and the default error code `\E_USER_ERROR`.
     *
     * @since v0.1.0
     */
    public function testReadFactory()
    {

        $ex = FileAccessException::Read( '/probe/read.txt' );

        $this->assertInstanceOf( FileAccessException::class, $ex );
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/probe/read.txt]! Could not read file.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( '/probe/read.txt', $ex->getPath() );

        // Every call returns a new, independent instance.
        $ex2 = FileAccessException::Read( '/probe/read.txt' );
        $this->assertNotEquals( $ex, $ex2 );

    }

    /**
     * Test the factory method `Read()` with the `$message` parameter: the
     * parameter is declared soft-nullable (`string $message = null`), so
     * `NULL` may be passed explicit without any `TypeError`.
     *
     * @since v0.1.0
     */
    public function testReadFactoryMessage()
    {

        // The message parameter is optional.
        $ex = FileAccessException::Read( '/f.txt' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read file.', $ex->getMessage() );

        // Explicit NULL is accepted (soft-nullable parameter).
        $ex = FileAccessException::Read( '/f.txt', null );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read file.', $ex->getMessage() );

        // An empty string behaves like no message.
        $ex = FileAccessException::Read( '/f.txt', '' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read file.', $ex->getMessage() );

        // A real message is appended to the message, that was built from the
        // access type.
        $ex = FileAccessException::Read( '/f.txt', 'file is empty' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read file. file is empty', $ex->getMessage() );
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );

    }

    /**
     * Test the factory method `Read()` with the `$code` parameter as `int`.
     *
     * @since v0.1.0
     */
    public function testReadFactoryCodeInt()
    {

        $ex = FileAccessException::Read( '/f.txt', 'appendix', 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read file. appendix', $ex->getMessage() );

        $ex = FileAccessException::Read( '/f.txt', 'appendix', \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

    }

    /**
     * Test the factory method `Read()` with the `$code` parameter as string:
     * in contrast to the constructor the factory methods declare the code as
     * `int`, so every string is rejected with a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testReadFactoryCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FileAccessException::Read(): Argument #3 ($code) must be of type int, string given' );

        FileAccessException::Read( '/f.txt', 'appendix', 'MY-CODE' );

    }

    /**
     * Test the factory method `Read()` with a numeric string as code: also
     * these are rejected because of the `int` parameter type.
     *
     * @since v0.1.0
     */
    public function testReadFactoryCodeNumericString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FileAccessException::Read(): Argument #3 ($code) must be of type int, string given' );

        FileAccessException::Read( '/f.txt', 'appendix', '4711' );

    }

    /**
     * Test the static factory method `Write()`: it must build a new instance
     * with the access type `ACCESS_WRITE`.
     *
     * @since v0.1.0
     */
    public function testWriteFactory()
    {

        $ex = FileAccessException::Write( '/probe/write.txt' );

        $this->assertInstanceOf( FileAccessException::class, $ex );
        $this->assertSame( FileAccessException::ACCESS_WRITE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/probe/write.txt]! Could not write file.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( '/probe/write.txt', $ex->getPath() );

    }

    /**
     * Test the factory method `Write()` with the optional `$message` and the
     * `$code` parameter.
     *
     * @since v0.1.0
     */
    public function testWriteFactoryMessageAndCode()
    {

        $ex = FileAccessException::Write( '/f.txt', null );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file.', $ex->getMessage() );

        $ex = FileAccessException::Write( '/f.txt', 'no permisson' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file. no permisson', $ex->getMessage() );
        $this->assertSame( FileAccessException::ACCESS_WRITE, $ex->getAccessType() );

        $ex = FileAccessException::Write( '/f.txt', 'no permisson', 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file. no permisson', $ex->getMessage() );

        // A "0" appendix is lost (findings see the journal).
        $ex = FileAccessException::Write( '/f.txt', '0' );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file.', $ex->getMessage() );

    }

    /**
     * Test the factory method `Write()` with a string as code: it is rejected
     * with a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testWriteFactoryCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FileAccessException::Write(): Argument #3 ($code) must be of type int, string given' );

        FileAccessException::Write( '/f.txt', 'appendix', 'MY-CODE' );

    }

    /**
     * Test the `$previous` parameter of the constructor.
     *
     * @since v0.1.0
     */
    public function testConstructorPrevious()
    {

        // Without previous exception.
        $ex = new FileAccessException( '/f.txt' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A plain `\Exception` may be used as previous exception.
        $prev = new \Exception( 'prev msg' );
        $ex = new FileAccessException( '/f.txt', 'read', 'appendix', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );

        // A `NiirrtyException` may be used as previous exception too, and the
        // previous exception has nothing to do with the own message.
        $prev = new NiirrtyException( 'prev Niirrty msg', 4712 );
        $ex = new FileAccessException( '/f.txt', 'write', 'appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( NiirrtyException::class, $ex->getPrevious() );
        $this->assertSame( 'prev Niirrty msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 4712, $ex->getPrevious()->getCode() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not write file. appendix', $ex->getMessage() );

        // A `FileAccessException` may be the previous exception of another one.
        $prev = new FileAccessException( '/prev.txt', 'delete' );
        $ex = new FileAccessException( '/f.txt', 'create', null, 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FileAccessException::class, $ex->getPrevious() );
        $this->assertSame( 'delete', $ex->getPrevious()->getAccessType() );
        $this->assertSame( 'create', $ex->getAccessType() );

    }

    /**
     * Test the `$previous` parameter of the factory methods.
     *
     * @since v0.1.0
     */
    public function testFactoryPrevious()
    {

        $prev = new \Exception( 'prev msg' );

        $ex = FileAccessException::Read( '/f.txt', 'read appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( FileAccessException::ACCESS_READ, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f.txt]! Could not read file. read appendix', $ex->getMessage() );

        $ex = FileAccessException::Write( '/f.txt', 'write appendix', 4712, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 4712, $ex->getCode() );
        $this->assertSame( FileAccessException::ACCESS_WRITE, $ex->getAccessType() );

        // Without previous exception the `$previous` parameter is optional.
        $ex = FileAccessException::Write( '/f.txt' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

    }

    /**
     * Test the class hierarchy: a `FileAccessException` is a `Niirrty\IO\IOException`,
     * a `Niirrty\NiirrtyException`, an `\Exception` and a `\Throwable`, but not
     * the other way round.
     *
     * @since v0.1.0
     */
    public function testClassHierarchy()
    {

        $ex = new FileAccessException( '/f.txt' );

        $this->assertInstanceOf( FileAccessException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( $ex instanceof \Throwable );

        // An ordinary `IOException` is NO `FileAccessException`.
        $ioex = new IOException( '/f.txt' );
        $this->assertNotInstanceOf( FileAccessException::class, $ioex );
        $this->assertSame( 'There was a error with path [/f.txt]!', $ioex->getMessage() );

    }

    /**
     * Test the read-only property `$access`: it is a private, read-only
     * promoted property, so it may not be read nor written from outside the
     * class.
     *
     * @since v0.1.0
     */
    public function testAccessPropertyReadonly()
    {

        // Reflection informations about the promoted property.
        $rp = new \ReflectionProperty( FileAccessException::class, 'access' );
        $this->assertSame( 'access', $rp->getName() );
        $this->assertTrue( $rp->isPrivate() );
        $this->assertTrue( $rp->isReadOnly() );
        $this->assertTrue( $rp->isPromoted() );
        $this->assertFalse( $rp->hasDefaultValue() );

        // Reading the property from outside fails with an `Error`.
        $ex = new FileAccessException( '/f.txt' );

        $this->expectException( \Error::class );
        $this->expectExceptionMessage( 'Cannot access private property Niirrty\IO\FileAccessException::$access' );

        $this->assertNull( $ex->access );

    }

    /**
     * Test to write the read-only property `$access` from outside the class:
     * because the property is private, the assignment fails with an `Error`
     * even before the read-only violation could be reported.
     *
     * @since v0.1.0
     */
    public function testAccessPropertyWriteForbidden()
    {

        $this->expectException( \Error::class );
        $this->expectExceptionMessage( 'Cannot access private property Niirrty\IO\FileAccessException::$access' );

        $ex = new FileAccessException( '/f.txt' );

        $ex->access = 'write';

    }

    /**
     * Test the access type of an instance, that is thrown and catched: neither
     * the access type nor the message may change when throwing.
     *
     * @since v0.1.0
     */
    public function testThrowAndCatch()
    {

        try
        {
            throw new FileAccessException( '/probe/locked.txt', FileAccessException::ACCESS_WRITE, 'file is locked' );
        }
        catch ( FileAccessException $ex )
        {
            $this->assertSame( FileAccessException::ACCESS_WRITE, $ex->getAccessType() );
            $this->assertSame( 'There was a error with path [/probe/locked.txt]! Could not write file. file is locked', $ex->getMessage() );
            $this->assertSame( '/probe/locked.txt', $ex->getPath() );
            $this->assertSame( 256, $ex->getCode() );
        }

        // Because it extends from `IOException` it may also be catched as one.
        try
        {
            throw FileAccessException::Write( '/probe/locked.txt' );
        }
        catch ( IOException $ex )
        {
            $this->assertInstanceOf( FileAccessException::class, $ex );
            $this->assertSame( FileAccessException::ACCESS_WRITE, $ex->getAccessType() );
        }

    }

    /**
     * Test the inherited `getErrorMessage()` method for the default error code
     * 256 (`\E_USER_ERROR`), a code that works (see the findings of task 01
     * for any other code).
     *
     * @since v0.1.0
     */
    public function testInheritedGetErrorMessage()
    {

        $ex = new FileAccessException( '/probe/file.txt' );

        $this->assertSame( 'ERROR(256): There was a error with path [/probe/file.txt]! Could not read file.', $ex->getErrorMessage() );

    }

    #endregion


}
