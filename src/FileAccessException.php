<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2026, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.6.4
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


    #region // = = =   C O N S T R U C T O R   = = = = = = = = = = = = = = = = = = = = = = = = = = = =

    /**
     * Init a new instance.
     *
     * @param string          $file     The file where accessing fails
     * @param FileAccess      $access   The access type (see FileAccess} enum
     * @param string|null     $message  The optional error message
     * @param string|int      $code     optional error code (Defaults to \E_USER_ERROR)
     * @param \Throwable|null $previous Optional previous exception.
     */
    public function __construct(
        string      $file,
        private readonly FileAccess $access = FileAccess::Unknown,
        ?string $message = null,
        string|int $code = 256,
        ?\Throwable $previous = null )
    {

        parent::__construct(
            $file,
            \sprintf( 'Could not %s file.', $access->toString() ) . static::appendMessage( $message ),
            $code,
            $previous
        );

    }

    #endregion


    #region // - - -   P U B L I C   M E T H O D S   - - - - - - - - - - - - - - - - - - - - - - - - -

    /**
     * Returns the error file access type (see {@see FileAccess} enum)
     *
     * @return FileAccess
     */
    public final function getAccessType(): FileAccess
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
        string $file, ?string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    : FileAccessException
    {

        return new FileAccessException (
            $file,
            FileAccess::Read,
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
        string $file, ?string $message = null, int $code = \E_USER_ERROR, ?\Throwable $previous = null )
    : FileAccessException
    {

        return new FileAccessException (
            $file,
            FileAccess::Write,
            $message,
            $code,
            $previous
        );

    }

    #endregion


}

