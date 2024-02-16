<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2021, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.4.0
 */


declare( strict_types = 1 );


namespace Niirrty\IO;


/**
 * This exception should be used if accessing a file for reading and/or writing fails.
 *
 * The class extends from {@see IOException}.
 *
 * @since v0.1
 */
class FileAccessException extends IOException
{


    #region // = = =   C O N S T A N T S   = = = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Reading file access.
     */
    public final const ACCESS_READ = 'read';

    /**
     * Writing file access.
     */
    public final const ACCESS_WRITE = 'write';

    /**
     * Reading and writing file access.
     */
    public final const ACCESS_READWRITE = 'read and write';

    /**
     * Creating file access.
     */
    public final const ACCESS_CREATE = 'create';

    /**
     * Deleting file access.
     */
    public final const ACCESS_DELETE = 'delete';

    #endregion


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Init a new instance.
     *
     * @param string          $file     The file where accessing fails
     * @param string          $access   The access type (see \UK\IO\FileAccessException::ACCESS_* class constants)
     * @param string|null     $message  The optional error message
     * @param string|int      $code     optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous Optional previous exception.
     */
    public function __construct(
        string      $file, private readonly string $access = self::ACCESS_READ, ?string $message = null, string|int $code = 256,
        ?\Throwable $previous = null )
    {

        parent::__construct(
            $file,
            \sprintf('Could not %s file.', $access) . static::appendMessage($message),
            $code,
            $previous
        );

    }

    #endregion


    #region // - - -   P U B L I C   M E T H O D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Returns the error file access type (see \UK\IO\FileAccessException::ACCESS_* class constants)
     *
     * @return string
     */
    public final function getAccessType(): string
    {
        return $this->access;
    }

    #endregion


    #region // - - -   P U B L I C   S T A T I C   M E T H O D S   - - - - - - - - - - - - - - - - - -

    /**
     * Init a new \Niirrty\IO\FileAccessException for file read mode.
     *
     * @param string          $file     The file where reading fails.
     * @param string|null     $message  The optional error message.
     * @param integer         $code     optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous Optional previous exception.
     *
     * @return FileAccessException
     */
    public static function Read(
        string $file, string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    : FileAccessException
    {

        return new FileAccessException (
            $file,
            FileAccessException::ACCESS_READ,
            $message,
            $code,
            $previous
        );

    }

    /**
     * Init a new \UK\IO\FileAccessException for file write mode.
     *
     * @param string          $file     The file where reading fails.
     * @param string|null     $message  The optional error message.
     * @param integer         $code     optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous Optional previous exception.
     *
     * @return FileAccessException
     */
    public static function Write(
        string $file, string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    : FileAccessException
    {

        return new FileAccessException (
            $file,
            FileAccessException::ACCESS_WRITE,
            $message,
            $code,
            $previous
        );

    }

    #endregion


}

