<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  (c) 2017, Ni Irrty
 * @license    MIT
 * @package    Niirrty\Tests\IO
 * @since      2026-11-16
 * @version    0.1.0
 */


declare( strict_types = 1 );


namespace Niirrty\Tests\IO;


use Niirrty\IO\FolderAccessException;
use Niirrty\IO\FolderNotFoundException;
use Niirrty\IO\IOException;
use Niirrty\NiirrtyException;
use Niirrty\PhpException;
use PHPUnit\Framework\TestCase;


/**
 * Test case for the class `Niirrty\IO\FolderAccessException`.
 *
 * The source in `src/` is NOT changed, the tests assert the CURRENT behaviour
 * (see the "Findings" section of the journal): different to
 * `Niirrty\IO\FileAccessException` this class declares NO first-use deprecation
 * because all optional parameters are explicitly nullable, and the full message
 * is `'There was a error with path [<folder>]! Could not <access> folder.'`.
 *
 * @since v0.1.0
 */
class FolderAccessExceptionTest extends TestCase
{


    #region // = = =   T E S T   M E T H O D S  = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Test the class definition itself: different to `Niirrty\IO\FileAccessException`
     * the FIRST use of this class does NOT throw anything. Therefore the class
     * can be used the ordinary way immediately.
     *
     * @since v0.1.0
     */
    public function testClassDefinitionWithoutDeprecation()
    {

        // All optional parameters of this class use the explicit nullable form
        // (`?string $message`, `?\Throwable $previous`), so no PHP 8.5
        // "implicitly nullable parameter" deprecation is emitted at the class
        // definition, as it is for `Niirrty\IO\FileAccessException`.
        $ex = new FolderAccessException( '/probe/folder' );

        $this->assertInstanceOf( FolderAccessException::class, $ex );
        $this->assertSame( 'There was a error with path [/probe/folder]! Could not read folder.', $ex->getMessage() );

    }

    /**
     * Test the 5 `ACCESS_*` class constants: their names must exist and their
     * values must be exactly as declared in the source.
     *
     * @since v0.1.0
     */
    public function testClassConstants()
    {

        // The constant names exist...
        $this->assertTrue( \defined( '\Niirrty\IO\FolderAccessException::ACCESS_READ' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FolderAccessException::ACCESS_WRITE' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FolderAccessException::ACCESS_READWRITE' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FolderAccessException::ACCESS_CREATE' ) );
        $this->assertTrue( \defined( '\Niirrty\IO\FolderAccessException::ACCESS_DELETE' ) );
        // ...and unknown ones don't.
        $this->assertFalse( \defined( '\Niirrty\IO\FolderAccessException::ACCESS_UNKNOWN' ) );

        // The constant values.
        $this->assertSame( 'read', FolderAccessException::ACCESS_READ );
        $this->assertSame( 'write', FolderAccessException::ACCESS_WRITE );
        $this->assertSame( 'read and write', FolderAccessException::ACCESS_READWRITE );
        $this->assertSame( 'create', FolderAccessException::ACCESS_CREATE );
        $this->assertSame( 'delete', FolderAccessException::ACCESS_DELETE );

        // The values are "folder" and not "file" dependend.
        $this->assertNotSame( 'file', FolderAccessException::ACCESS_READ );

        // The constants are used "case sensitively".
        $this->assertNotSame( 'READ', FolderAccessException::ACCESS_READ );

        // The 5 constants in declaration order.
        $this->assertSame( [
            'ACCESS_READ', 'ACCESS_WRITE', 'ACCESS_READWRITE', 'ACCESS_CREATE', 'ACCESS_DELETE',
        ], \array_keys( ( new \ReflectionClass( FolderAccessException::class ) )->getConstants() ) );

    }

    /**
     * Test the class constants by reflection: they are implicitly public, and
     * FINDINGS: different to `FileAccessException` they are NOT declared `final`
     * in the source, so `isFinal()` is false for every one of them.
     *
     * @since v0.1.0
     */
    public function testClassConstantsReflection()
    {

        $rc = new \ReflectionClass( FolderAccessException::class );

        $names = [];
        foreach( $rc->getReflectionConstants() as $ct )
        {
            $names[] = $ct->getName();
            $this->assertTrue( $ct->isPublic() );
            $this->assertFalse( $ct->isPrivate() );
            // FINDINGS: the source declares the constants without the `final`
            // and without the `public` modifier, in contrast to
            // `Niirrty\IO\FileAccessException`, where all 5 constants are
            // `public final const`.
            $this->assertFalse( $ct->isFinal() );
        }

        $this->assertSame( ['ACCESS_READ', 'ACCESS_WRITE', 'ACCESS_READWRITE', 'ACCESS_CREATE', 'ACCESS_DELETE'], $names );

        $this->assertTrue( $rc->hasConstant( 'ACCESS_DELETE' ) );
        $this->assertFalse( $rc->hasConstant( 'ACCESS_UNKNOWN' ) );
        $this->assertSame( 'delete', $rc->getConstant( 'ACCESS_DELETE' ) );

    }

    /**
     * Test the constructor with the folder only: the access type must default
     * to `ACCESS_READ`, the message must be built and the error code must
     * default to 256 (`\E_USER_ERROR`).
     *
     * @since v0.1.0
     */
    public function testConstructorDefaults()
    {

        $ex = new FolderAccessException( '/probe/folder' );

        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );
        // The message from the source ("Could not %s folder.") is appended to
        // the `IOException` message, which was build from the folder.
        $this->assertSame( 'There was a error with path [/probe/folder]! Could not read folder.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( \E_USER_ERROR, $ex->getCode() );
        // The folder is forwarded to the `IOException` and stays readable.
        $this->assertSame( '/probe/folder', $ex->getPath() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

    }

    /**
     * Test the constructor with every `ACCESS_*` constant given explicit: for
     * each one the access type must be kept and the message must be
     * "Could not <access> folder.".
     *
     * @since v0.1.0
     */
    public function testConstructorAccessType()
    {

        $ex = new FolderAccessException( '/f', FolderAccessException::ACCESS_READ );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder.', $ex->getMessage() );

        $ex = new FolderAccessException( '/f', FolderAccessException::ACCESS_WRITE );
        $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder.', $ex->getMessage() );

        $ex = new FolderAccessException( '/f', FolderAccessException::ACCESS_READWRITE );
        $this->assertSame( FolderAccessException::ACCESS_READWRITE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not read and write folder.', $ex->getMessage() );

        $ex = new FolderAccessException( '/f', FolderAccessException::ACCESS_CREATE );
        $this->assertSame( FolderAccessException::ACCESS_CREATE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not create folder.', $ex->getMessage() );

        $ex = new FolderAccessException( '/f', FolderAccessException::ACCESS_DELETE );
        $this->assertSame( FolderAccessException::ACCESS_DELETE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not delete folder.', $ex->getMessage() );

    }

    /**
     * Test the constructor with an access type, that is none of the `ACCESS_*`
     * constants: the value is NOT validated, it is simply used in the message.
     *
     * @since v0.1.0
     */
    public function testConstructorAccessFreeValue()
    {

        $ex = new FolderAccessException( '/f', 'flush' );

        $this->assertSame( 'flush', $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not flush folder.', $ex->getMessage() );

        // Also an empty access type is accepted.
        $ex = new FolderAccessException( '/f', '' );
        $this->assertSame( '', $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not  folder.', $ex->getMessage() );

        // Even a value with its own whitespace is used "as is".
        $ex = new FolderAccessException( '/f', ' read  the ' );
        $this->assertSame( ' read  the ', $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not  read  the  folder.', $ex->getMessage() );

        // The access type has nothing to do with the folder.
        $this->assertSame( '/f', $ex->getPath() );

    }

    /**
     * Test the constructor with `NULL` as access type: although the parameter
     * has a default value, its type is `string` and - different to ordinary
     * parameters - promoted property parameters may NOT be implicitly nullable,
     * so passing `NULL` is a `TypeError`.
     *
     * @since v0.1.0
     */
    public function testConstructorAccessNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #2 ($access) must be of type string, null given' );

        new FolderAccessException( '/f', null );

    }

    /**
     * Test the constructor with `NULL` as folder: the folder parameter is not
     * nullable at all.
     *
     * @since v0.1.0
     */
    public function testConstructorFolderNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($folder) must be of type string, null given' );

        new FolderAccessException( null );

    }

    /**
     * Test the constructor with a folder, that is no string: because this test
     * file uses `declare( strict_types = 1 )` the `string` parameter rejects
     * also an `int` (without strict types it would be converted silently).
     *
     * @since v0.1.0
     */
    public function testConstructorFolderInt()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #1 ($folder) must be of type string, int given' );

        new FolderAccessException( 4711 );

    }

    /**
     * Test the optional error message appendix: `NiirrtyException::appendMessage()`
     * decides with `empty()` whether to append something or not.
     *
     * @since v0.1.0
     */
    public function testConstructorMessageAppendix()
    {

        // NULL appendix behaves like no appendix at all.
        $ex = new FolderAccessException( '/f', 'write', null );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder.', $ex->getMessage() );

        // An empty appendix behaves like no appendix.
        $ex = new FolderAccessException( '/f', 'write', '' );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder.', $ex->getMessage() );

        // A real appendix is appended, separated by a single space.
        $ex = new FolderAccessException( '/f', 'write', 'disk full' );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder. disk full', $ex->getMessage() );

        // A blank appendix is appended also, so the message ends with 2 spaces.
        $ex = new FolderAccessException( '/f', 'write', ' ' );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder.  ', $ex->getMessage() );

        // Findings: a "0" appendix is lost because `appendMessage()` tests the
        // appendix with `empty()`.
        $ex = new FolderAccessException( '/f', 'write', '0' );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder.', $ex->getMessage() );

    }

    /**
     * Test the `$code` parameter of the constructor with `int` values.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeInt()
    {

        $ex = new FolderAccessException( '/f', 'read', null, 4711 );
        $this->assertSame( 4711, $ex->getCode() );

        $ex = new FolderAccessException( '/f', 'read', null, 0 );
        $this->assertSame( 0, $ex->getCode() );

        $ex = new FolderAccessException( '/f', 'read', null, \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

        $ex = new FolderAccessException( '/f', 'read', null, -1 );
        $this->assertSame( -1, $ex->getCode() );

        // The code has nothing to do with the access type.
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );

    }

    /**
     * Test the `$code` parameter of the constructor with an alphanumeric string:
     * the signature documents `string|int` but the inner `\Exception`
     * constructor rejects it.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FolderAccessException( '/f', 'read', 'appendix', 'MY-CODE' );

    }

    /**
     * Test the `$code` parameter of the constructor with a numeric string: also
     * these are rejected by the inner `\Exception` constructor.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeNumericString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Exception::__construct(): Argument #2 ($code) must be of type int, string given' );

        new FolderAccessException( '/f', 'read', 'appendix', '4711' );

    }

    /**
     * Test the `$code` parameter of the constructor with a `float`: because of
     * the `string|int` parameter type and `declare( strict_types = 1 )` it is
     * rejected directly, without any implicit conversion.
     *
     * @since v0.1.0
     */
    public function testConstructorCodeFloat()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #4 ($code) must be of type string|int, float given' );

        new FolderAccessException( '/f', 'read', 'appendix', 256.5 );

    }

    /**
     * Test the constructor with a not nullable string as optional `$message`:
     * the message parameter is explicitly nullable, so a `NULL` is accepted
     * (see `testConstructorMessageAppendix()`) but an `int` is not.
     *
     * @since v0.1.0
     */
    public function testConstructorMessageNotString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'Argument #3 ($message) must be of type ?string, int given' );

        new FolderAccessException( '/f', 'read', 4711 );

    }

    /**
     * Test the `$previous` parameter of the constructor.
     *
     * @since v0.1.0
     */
    public function testConstructorPrevious()
    {

        // Without previous exception.
        $ex = new FolderAccessException( '/f' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // Explicit NULL behaves like no previous exception at all.
        $ex = new FolderAccessException( '/f', 'read', 'appendix', 256, null );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A plain `\Exception` may be used as previous exception.
        $prev = new \Exception( 'prev msg' );
        $ex = new FolderAccessException( '/f', 'read', 'appendix', 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );

        // A `NiirrtyException` may be used as previous exception too, and the
        // previous exception has nothing to do with the own message.
        $prev = new NiirrtyException( 'prev Niirrty msg', 4712 );
        $ex = new FolderAccessException( '/f', 'write', 'appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( NiirrtyException::class, $ex->getPrevious() );
        $this->assertSame( 'prev Niirrty msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 4712, $ex->getPrevious()->getCode() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder. appendix', $ex->getMessage() );

        // An `IOException` may be the previous exception.
        $prev = new IOException( '/prev/path' );
        $ex = new FolderAccessException( '/f', 'create', null, 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( IOException::class, $ex->getPrevious() );
        $this->assertSame( '/prev/path', $ex->getPrevious()->getPath() );
        $this->assertSame( FolderAccessException::ACCESS_CREATE, $ex->getAccessType() );

        // A `FolderAccessException` may be the previous exception of another one.
        $prev = new FolderAccessException( '/prev/folder', 'delete' );
        $ex = new FolderAccessException( '/f', 'create', null, 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FolderAccessException::class, $ex->getPrevious() );
        $this->assertSame( 'delete', $ex->getPrevious()->getAccessType() );
        $this->assertSame( 'create', $ex->getAccessType() );

    }

    /**
     * Test `getAccessType()`: for instances, that where built with the
     * constructor as well as for the ones from the factory methods.
     *
     * @since v0.1.0
     */
    public function testGetAccessType()
    {

        $ex = new FolderAccessException( '/f' );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );
        // Calling it more then once must be without side effects.
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );

        $ex = new FolderAccessException( '/f', FolderAccessException::ACCESS_DELETE );
        $this->assertSame( FolderAccessException::ACCESS_DELETE, $ex->getAccessType() );

        $ex = FolderAccessException::Read( '/f' );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );

        $ex = FolderAccessException::Write( '/f' );
        $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );

        // An arbitrary access type is returned "as it was given".
        $ex = new FolderAccessException( '/f', 'mount' );
        $this->assertSame( 'mount', $ex->getAccessType() );

        // An empty access type is returned as empty string.
        $ex = new FolderAccessException( '/f', '' );
        $this->assertSame( '', $ex->getAccessType() );

    }

    /**
     * Test `getAccessType()` by reflection: it is a public, non static method
     * with an own `string` return type and without parameters, and it is
     * declared `final`, so no subclass may override it.
     *
     * @since v0.1.0
     */
    public function testGetAccessTypeFinal()
    {

        $rm = new \ReflectionMethod( FolderAccessException::class, 'getAccessType' );

        $this->assertTrue( $rm->isPublic() );
        $this->assertFalse( $rm->isPrivate() );
        $this->assertFalse( $rm->isStatic() );
        $this->assertFalse( $rm->isAbstract() );
        $this->assertTrue( $rm->isUserDefined() );
        $this->assertTrue( $rm->isFinal() );
        $this->assertSame( 0, $rm->getNumberOfParameters() );
        $this->assertSame( 0, $rm->getNumberOfRequiredParameters() );
        $this->assertTrue( $rm->hasReturnType() );
        $this->assertSame( 'string', (string) $rm->getReturnType() );
        $this->assertSame( 'getAccessType', $rm->getShortName() );

        // Also the `getPath()` method, that is inherited from the
        // `Niirrty\IO\IOException`, is declared `final`.
        $rm = new \ReflectionMethod( FolderAccessException::class, 'getPath' );
        $this->assertTrue( $rm->isFinal() );
        $this->assertSame( 'Niirrty\IO\IOException', $rm->getDeclaringClass()->getName() );

    }

    /**
     * Test the static factory method `Read()`: it must build a new instance
     * with the access type `ACCESS_READ` and the default error code `\E_USER_ERROR`.
     *
     * @since v0.1.0
     */
    public function testReadFactory()
    {

        $ex = FolderAccessException::Read( '/probe/read' );

        $this->assertInstanceOf( FolderAccessException::class, $ex );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/probe/read]! Could not read folder.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( '/probe/read', $ex->getPath() );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

    }

    /**
     * Test the factory method `Read()` with the optional `$message` parameter:
     * different to `FileAccessException::Read()` its parameter is declared
     * explicitly nullable (`?string $message = null`), so an explicit `NULL`
     * is accepted without any deprecation.
     *
     * @since v0.1.0
     */
    public function testReadFactoryMessage()
    {

        // The message parameter is optional.
        $ex = FolderAccessException::Read( '/f' );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder.', $ex->getMessage() );

        // Explicit NULL is accepted (explicit nullable parameter).
        $ex = FolderAccessException::Read( '/f', null );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder.', $ex->getMessage() );

        // An empty string behaves like no message.
        $ex = FolderAccessException::Read( '/f', '' );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder.', $ex->getMessage() );

        // A real message is appended to the message, that was built from the
        // access type.
        $ex = FolderAccessException::Read( '/f', 'folder is empty' );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder. folder is empty', $ex->getMessage() );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );

        // A "0" appendix is lost (findings see the journal).
        $ex = FolderAccessException::Read( '/f', '0' );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder.', $ex->getMessage() );

    }

    /**
     * Test the factory method `Read()` with the `$code` parameter as `int`.
     *
     * @since v0.1.0
     */
    public function testReadFactoryCodeInt()
    {

        $ex = FolderAccessException::Read( '/f', 'appendix', 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder. appendix', $ex->getMessage() );

        $ex = FolderAccessException::Read( '/f', 'appendix', \E_USER_ERROR );
        $this->assertSame( 256, $ex->getCode() );

        // The default code is `\E_USER_ERROR` (256) as well.
        $ex = FolderAccessException::Read( '/f', 'appendix' );
        $this->assertSame( 256, $ex->getCode() );

        $ex = FolderAccessException::Read( '/f', null, 0 );
        $this->assertSame( 0, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder.', $ex->getMessage() );

    }

    /**
     * Test the factory method `Read()` with the `$code` parameter as string:
     * in contrast to the constructor the factory methods declare the code as
     * `int`, so with `declare( strict_types = 1 )` every string is rejected.
     *
     * @since v0.1.0
     */
    public function testReadFactoryCodeString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FolderAccessException::Read(): Argument #3 ($code) must be of type int, string given' );

        FolderAccessException::Read( '/f', 'appendix', 'MY-CODE' );

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
        $this->expectExceptionMessage( 'FolderAccessException::Read(): Argument #3 ($code) must be of type int, string given' );

        FolderAccessException::Read( '/f', 'appendix', '4711' );

    }

    /**
     * Test the factory method `Read()` with `NULL` as code: the parameter has
     * a default value but its type is the not nullable `int`.
     *
     * @since v0.1.0
     */
    public function testReadFactoryCodeNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FolderAccessException::Read(): Argument #3 ($code) must be of type int, null given' );

        FolderAccessException::Read( '/f', 'appendix', null );

    }

    /**
     * Test the factory method `Read()` with a `$message`, that is no string.
     *
     * @since v0.1.0
     */
    public function testReadFactoryMessageNotString()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FolderAccessException::Read(): Argument #2 ($message) must be of type ?string, int given' );

        FolderAccessException::Read( '/f', 4711 );

    }

    /**
     * Test the static factory method `Write()`: it must build a new instance
     * with the access type `ACCESS_WRITE`.
     *
     * @since v0.1.0
     */
    public function testWriteFactory()
    {

        $ex = FolderAccessException::Write( '/probe/write' );

        $this->assertInstanceOf( FolderAccessException::class, $ex );
        $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/probe/write]! Could not write folder.', $ex->getMessage() );
        $this->assertSame( 256, $ex->getCode() );
        $this->assertSame( '/probe/write', $ex->getPath() );

    }

    /**
     * Test the factory method `Write()` with the optional `$message` and the
     * `$code` parameter.
     *
     * @since v0.1.0
     */
    public function testWriteFactoryMessageAndCode()
    {

        $ex = FolderAccessException::Write( '/f', null );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder.', $ex->getMessage() );

        $ex = FolderAccessException::Write( '/f', 'no permisson' );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder. no permisson', $ex->getMessage() );
        $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );

        $ex = FolderAccessException::Write( '/f', 'no permisson', 4711 );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder. no permisson', $ex->getMessage() );

        // A "0" appendix is lost (findings see the journal).
        $ex = FolderAccessException::Write( '/f', '0' );
        $this->assertSame( 'There was a error with path [/f]! Could not write folder.', $ex->getMessage() );

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
        $this->expectExceptionMessage( 'FolderAccessException::Write(): Argument #3 ($code) must be of type int, string given' );

        FolderAccessException::Write( '/f', 'appendix', 'MY-CODE' );

    }

    /**
     * Test the factory method `Write()` with `NULL` as code.
     *
     * @since v0.1.0
     */
    public function testWriteFactoryCodeNull()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FolderAccessException::Write(): Argument #3 ($code) must be of type int, null given' );

        FolderAccessException::Write( '/f', 'appendix', null );

    }

    /**
     * Test the `$previous` parameter of the factory methods.
     *
     * @since v0.1.0
     */
    public function testFactoryPrevious()
    {

        $prev = new \Exception( 'prev msg' );

        $ex = FolderAccessException::Read( '/f', 'read appendix', 4711, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 'prev msg', $ex->getPrevious()->getMessage() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );
        $this->assertSame( 'There was a error with path [/f]! Could not read folder. read appendix', $ex->getMessage() );

        $ex = FolderAccessException::Write( '/f', 'write appendix', 4712, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertSame( $prev, $ex->getPrevious() );
        $this->assertSame( 4712, $ex->getCode() );
        $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );

        // Without previous exception the `$previous` parameter is optional.
        $ex = FolderAccessException::Write( '/f' );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // Explicit `NULL` behaves like no previous exception.
        $ex = FolderAccessException::Read( '/f', 'appendix', 256, null );
        $this->assertFalse( $ex->hasPrevious() );
        $this->assertNull( $ex->getPrevious() );

        // A `FolderAccessException` may be the previous exception of another
        // one, also if it was build by a factory method.
        $prev = FolderAccessException::Write( '/prev/folder' );
        $ex = FolderAccessException::Read( '/f', null, 256, $prev );
        $this->assertTrue( $ex->hasPrevious() );
        $this->assertInstanceOf( FolderAccessException::class, $ex->getPrevious() );
        $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getPrevious()->getAccessType() );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );

    }

    /**
     * Test the `$previous` parameter of the factory methods with a value, that
     * is no `Throwable`.
     *
     * @since v0.1.0
     */
    public function testFactoryPreviousNotThrowable()
    {

        $this->expectException( \TypeError::class );
        $this->expectExceptionMessage( 'FolderAccessException::Write(): Argument #4 ($previous) must be of type ?Throwable, string given' );

        FolderAccessException::Write( '/f', 'appendix', 256, 'not a throwable' );

    }

    /**
     * Test the constructor as well as the factory methods with named
     * parameters (PHP 8+): the parameter names are `folder`, `access`
     * (constructor only), `message`, `code` and `previous`.
     *
     * @since v0.1.0
     */
    public function testNamedParameters()
    {

        $ex = new FolderAccessException( folder: '/probe/named', access: 'create', message: 'named appendix', code: 4711 );
        $this->assertSame( 'There was a error with path [/probe/named]! Could not create folder. named appendix', $ex->getMessage() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( 'create', $ex->getAccessType() );
        $this->assertSame( '/probe/named', $ex->getPath() );

        // The order of named parameters does not matter.
        $ex = new FolderAccessException( code: 4712, message: 'second', access: 'delete', folder: '/probe/other' );
        $this->assertSame( 'There was a error with path [/probe/other]! Could not delete folder. second', $ex->getMessage() );
        $this->assertSame( 4712, $ex->getCode() );
        $this->assertSame( 'delete', $ex->getAccessType() );

        $ex = FolderAccessException::Read( folder: '/probe/named', message: 'named', code: 4712 );
        $this->assertSame( 'There was a error with path [/probe/named]! Could not read folder. named', $ex->getMessage() );
        $this->assertSame( 4712, $ex->getCode() );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );

        $ex = FolderAccessException::Write( code: 4713, message: 'w', folder: '/probe/w' );
        $this->assertSame( 4713, $ex->getCode() );
        $this->assertSame( 'There was a error with path [/probe/w]! Could not write folder. w', $ex->getMessage() );
        $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );

    }

    /**
     * Test the factory methods by reflection: both are public static methods,
     * they are not final, they require only the folder and the 3 other
     * parameters are optional. Their return type is the class itself.
     *
     * @since v0.1.0
     */
    public function testFactoryMethodsReflection()
    {

        $names = [];
        $rm = new \ReflectionMethod( FolderAccessException::class, 'Read' );
        foreach( $rm->getParameters() as $pa )
        {
            $names[] = $pa->getName();
            $this->assertFalse( $pa->isPromoted() );
            $this->assertFalse( $pa->isVariadic() );
        }
        $this->assertSame( ['folder', 'message', 'code', 'previous'], $names );
        $this->assertTrue( $rm->isPublic() );
        $this->assertTrue( $rm->isStatic() );
        $this->assertFalse( $rm->isFinal() );
        $this->assertFalse( $rm->isVariadic() );
        $this->assertSame( 4, $rm->getNumberOfParameters() );
        $this->assertSame( 1, $rm->getNumberOfRequiredParameters() );
        $this->assertTrue( $rm->hasReturnType() );
        $this->assertSame( 'Niirrty\\IO\\FolderAccessException', (string) $rm->getReturnType() );

        $names = [];
        $rm = new \ReflectionMethod( FolderAccessException::class, 'Write' );
        foreach( $rm->getParameters() as $pa )
        {
            $names[] = $pa->getName();
        }
        $this->assertSame( ['folder', 'message', 'code', 'previous'], $names );
        $this->assertTrue( $rm->isStatic() );
        $this->assertSame( 4, $rm->getNumberOfParameters() );
        $this->assertSame( 1, $rm->getNumberOfRequiredParameters() );
        $this->assertSame( 'Niirrty\\IO\\FolderAccessException', (string) $rm->getReturnType() );

        // The constructor declares the promoted, read-only property `$access`.
        $rm = new \ReflectionMethod( FolderAccessException::class, '__construct' );
        $this->assertFalse( $rm->isStatic() );
        $this->assertTrue( $rm->isPublic() );
        $this->assertSame( 5, $rm->getNumberOfParameters() );
        $this->assertSame( 1, $rm->getNumberOfRequiredParameters() );
        $names = [];
        foreach( $rm->getParameters() as $pa )
        {
            $names[] = $pa->getName();
        }
        $this->assertSame( ['folder', 'access', 'message', 'code', 'previous'], $names );

        // The list of methods, that the class declares itself.
        $rc = new \ReflectionClass( FolderAccessException::class );
        $this->assertTrue( $rc->hasMethod( 'getAccessType' ) );
        $this->assertTrue( $rc->hasMethod( 'Read' ) );
        $this->assertTrue( $rc->hasMethod( 'Write' ) );
        // The class itself overrides neither `getPath()` nor `getErrorMessage()`.
        $this->assertSame( 'Niirrty\\IO\\IOException', new \ReflectionMethod( FolderAccessException::class, 'getPath' )->getDeclaringClass()->getName() );
        $this->assertSame( 'Niirrty\\NiirrtyException', new \ReflectionMethod( FolderAccessException::class, 'getErrorMessage' )->getDeclaringClass()->getName() );
        // Findings: also the `getMessage()` method is NOT overridden by the
        // Niirrty exception classes, it is the built-in one.
        $this->assertSame( 'Exception', new \ReflectionMethod( FolderAccessException::class, 'getMessage' )->getDeclaringClass()->getName() );

    }

    /**
     * Test the factory methods: every call returns a new, independent instance
     * (no singleton) and the access type is hardcoded.
     *
     * @since v0.1.0
     */
    public function testFactoryReturnsNewInstances()
    {

        $ex1 = FolderAccessException::Read( '/probe/same' );
        $ex2 = FolderAccessException::Read( '/probe/same' );

        $this->assertNotSame( $ex1, $ex2 );
        $this->assertSame( $ex1->getMessage(), $ex2->getMessage() );
        $this->assertSame( $ex1->getAccessType(), $ex2->getAccessType() );

        // The factory methods instantiate the class itself, so the access type
        // can never be changed over.
        $ex1 = FolderAccessException::Read( '/probe/read' );
        $this->assertNotSame( FolderAccessException::ACCESS_WRITE, $ex1->getAccessType() );
        $this->assertSame( FolderAccessException::ACCESS_READ, $ex1->getAccessType() );

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
        $rp = new \ReflectionProperty( FolderAccessException::class, 'access' );
        $this->assertSame( 'access', $rp->getName() );
        $this->assertTrue( $rp->isPrivate() );
        $this->assertTrue( $rp->isReadOnly() );
        $this->assertTrue( $rp->isPromoted() );
        $this->assertFalse( $rp->isStatic() );
        $this->assertFalse( $rp->isPublic() );
        // Findings: the default value of the promoted property is the class
        // constant `self::ACCESS_READ`, but reflection cannot retrieve it.
        $this->assertFalse( $rp->hasDefaultValue() );

        // Reading the property from outside fails with an `Error`.
        $ex = new FolderAccessException( '/f' );

        $this->expectException( \Error::class );
        $this->expectExceptionMessage( 'Cannot access private property Niirrty\IO\FolderAccessException::$access' );

        $ex->access;

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
        $this->expectExceptionMessage( 'Cannot access private property Niirrty\IO\FolderAccessException::$access' );

        $ex = new FolderAccessException( '/f' );

        $ex->access = 'write';

    }

    /**
     * Test the class hierarchy: a `FolderAccessException` is a
     * `Niirrty\IO\IOException`, a `Niirrty\NiirrtyException`, an `\Exception`
     * and a `\Throwable`, but not the other way round.
     *
     * @since v0.1.0
     */
    public function testClassHierarchy()
    {

        $ex = new FolderAccessException( '/f' );

        $this->assertInstanceOf( FolderAccessException::class, $ex );
        $this->assertInstanceOf( IOException::class, $ex );
        $this->assertInstanceOf( NiirrtyException::class, $ex );
        $this->assertInstanceOf( \Exception::class, $ex );
        $this->assertTrue( $ex instanceof \Throwable );
        $this->assertSame( 'Niirrty\\IO\\FolderAccessException', (string) \get_class( $ex ) );

        // An ordinary `IOException` is NO `FolderAccessException`.
        $ioex = new IOException( '/f' );
        $this->assertNotInstanceOf( FolderAccessException::class, $ioex );
        $this->assertSame( 'There was a error with path [/f]!', $ioex->getMessage() );

        // A `FolderNotFoundException` is a sibling, not a parent or child.
        $fnfex = new FolderNotFoundException( '/f' );
        $this->assertNotInstanceOf( FolderAccessException::class, $fnfex );
        $this->assertNotInstanceOf( FolderNotFoundException::class, $ex );
        $this->assertInstanceOf( IOException::class, $fnfex );

        // The subclass relationship can be read from reflection too.
        $rc = new \ReflectionClass( FolderAccessException::class );
        $this->assertSame( 'Niirrty\IO\IOException', $rc->getParentClass()->getName() );
        $this->assertFalse( (bool) \is_subclass_of( IOException::class, FolderAccessException::class ) );

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
            throw new FolderAccessException( '/probe/locked', FolderAccessException::ACCESS_WRITE, 'folder is locked' );
        }
        catch ( FolderAccessException $ex )
        {
            $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );
            $this->assertSame( 'There was a error with path [/probe/locked]! Could not write folder. folder is locked', $ex->getMessage() );
            $this->assertSame( '/probe/locked', $ex->getPath() );
            $this->assertSame( 256, $ex->getCode() );
        }

        // Because it extends from `IOException` it may also be catched as one.
        try
        {
            throw FolderAccessException::Write( '/probe/locked' );
        }
        catch ( IOException $ex )
        {
            $this->assertInstanceOf( FolderAccessException::class, $ex );
            $this->assertSame( FolderAccessException::ACCESS_WRITE, $ex->getAccessType() );
        }

        // The factory methods may also be used to throw directly.
        try
        {
            throw FolderAccessException::Read( '/probe/locked' );
        }
        catch ( FolderAccessException $ex )
        {
            $this->assertSame( FolderAccessException::ACCESS_READ, $ex->getAccessType() );
            $this->assertSame( 'There was a error with path [/probe/locked]! Could not read folder.', $ex->getMessage() );
        }

    }

    /**
     * Test the inherited `getErrorMessage()` method for the default error code
     * 256 (`\E_USER_ERROR`) - a code that works (see the findings of task 01
     * for any other code).
     *
     * @since v0.1.0
     */
    public function testInheritedGetErrorMessage()
    {

        $ex = new FolderAccessException( '/probe/folder' );

        $this->assertSame( 'ERROR(256): There was a error with path [/probe/folder]! Could not read folder.', $ex->getErrorMessage() );

        $ex = FolderAccessException::Write( '/probe/folder', 'appendix' );
        $this->assertSame( 'ERROR(256): There was a error with path [/probe/folder]! Could not write folder. appendix', $ex->getErrorMessage() );

    }

    /**
     * Test the inherited `getErrorMessage()` with a previous exception: the
     * previous message is appended, separated by a space (or a newline if
     * `$appendPreviousByNewline` is true).
     *
     * @since v0.1.0
     */
    public function testInheritedGetErrorMessageWithPrevious()
    {

        $prev = new IOException( '/prev/path' );
        $ex = new FolderAccessException( '/probe/folder', 'write', 'ap', 256, $prev );

        $this->assertSame( 'ERROR(256): There was a error with path [/probe/folder]! Could not write folder. ap ERROR(256): There was a error with path [/prev/path]!', $ex->getErrorMessage() );
        // With newline separator.
        $this->assertSame( "ERROR(256): There was a error with path [/probe/folder]! Could not write folder. ap\nERROR(256): There was a error with path [/prev/path]!", $ex->getErrorMessage( true ) );

    }

    /**
     * Test the inherited `getErrorMessage()` with an error code that is none
     * of the PHP error levels: `NiirrtyException::GetCodeName()` references the
     * deprecated constant `E_STRICT`, so the call throws a
     * `Niirrty\PhpException` (see the findings of task 01).
     *
     * @since v0.1.0
     */
    public function testInheritedGetErrorMessageNotUsable()
    {

        $ex = FolderAccessException::Read( '/probe/folder', 'ap', 4711 );

        $this->assertSame( 4711, $ex->getCode() );

        $this->expectException( PhpException::class );
        $this->expectExceptionMessage( 'Constant E_STRICT is deprecated since 8.4, the error level was removed' );

        $ex->getErrorMessage();

    }

    /**
     * Test the inherited `__toString()` (-> `toCustomString()`): it contains the
     * class name, the location, where the instance was build and the message.
     *
     * @since v0.1.0
     */
    public function testToString()
    {

        $ex = new FolderAccessException( '/probe/folder', 'delete', 'extra' );

        $str = (string) $ex;

        $this->assertSame( $ex->__toString(), $str );
        $this->assertTrue( (bool) \str_contains( $str, 'Niirrty\IO\FolderAccessException in ' ) );
        $this->assertTrue( (bool) \str_contains( $str, $ex->getMessage() ) );
        $this->assertTrue( (bool) \str_contains( $str, 'FolderAccessExceptionTest.php' ) );
        // The string representation is not identical to the message.
        $this->assertNotSame( $ex->getMessage(), $str );
        $this->assertNotSame( $ex->getErrorMessage(), $str );

    }

    /**
     * Test the inherited `getMessage()`, `getCode()`, `getPath()` and
     * `getPrevious()`: the class itself overrides none of them.
     *
     * @since v0.1.0
     */
    public function testInheritedAccessors()
    {

        $ex = FolderAccessException::Write( '/probe/folder', 'appendix', 4711 );

        $this->assertSame( 'There was a error with path [/probe/folder]! Could not write folder. appendix', $ex->getMessage() );
        $this->assertSame( 4711, $ex->getCode() );
        $this->assertSame( '/probe/folder', $ex->getPath() );
        $this->assertNull( $ex->getPrevious() );
        $this->assertFalse( $ex->hasPrevious() );

        // The path is returned "as it was given", also with spaces, umlauts and
        // backslashes.
        $ex = new FolderAccessException( 'c:\\Temp\\My Folder\\äöü' );
        $this->assertSame( 'c:\\Temp\\My Folder\\äöü', $ex->getPath() );
        $this->assertSame( 'There was a error with path [c:\\Temp\\My Folder\\äöü]! Could not read folder.', $ex->getMessage() );

        // Also an empty folder is accepted.
        $ex = new FolderAccessException( '' );
        $this->assertSame( '', $ex->getPath() );
        $this->assertSame( 'There was a error with path []! Could not read folder.', $ex->getMessage() );

    }

    #endregion


}
