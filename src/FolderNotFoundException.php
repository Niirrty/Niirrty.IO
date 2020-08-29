<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017-2020, Niirrty
 * @package    Niirrty\IO
 * @since      2017-11-01
 * @version    0.3.0
 */


declare( strict_types=1 );


namespace Niirrty\IO;


/**
 * This exception should be used if a required folder not exists.
 *
 * The class extends from {@see \Niirrty\IO\IOException}.
 *
 * @since v0.1
 */
class FolderNotFoundException extends IOException
{


    # <editor-fold desc="= = =   C O N S T R U C T O R   +   D E S T R U C T O R   = = = = = = = = = = = = = = =">


    /**
     * Init a new instance
     *
     * @param string     $folder   The missed folder.
     * @param string     $message  The optional error message
     * @param int        $code     The optional error code (Default to \E_USER_ERROR)
     * @param \Throwable $previous A optional previous exception
     */
    public function __construct(
        string $folder, string $message = null, int $code = \E_USER_ERROR, \Throwable $previous = null )
    {

        parent::__construct(
            $folder,
            'The Folder not exists.' . static::appendMessage( $message ),
            $code,
            $previous
        );

    }


    # </editor-fold>


}

