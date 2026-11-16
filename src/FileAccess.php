<?php
/**
 * @author     Ni Irrty <niirrty+code@gmail.com>
 * @copyright  © 2017 - 2026, Niirrty
 * @package    Niirrty\IO
 * @since      2026-11-26
 * @version    0.6.4
 */


declare( strict_types=1 );


namespace Niirrty\IO;

/**
 * This enum includes all available file access types
 */
enum FileAccess: int
{

    /**
     * Some unknown file access
     */
    case Unknown = 0;
    /**
     * Reading file access
     */
    case Read = 1;
    /**
     * Writing file access
     */
    case Write = 2;
    /**
     * Reading and writing file access
     */
    case ReadWrite = 3;
    /**
     * Creating file access
     */
    case Create = 4;
    /**
     * Deleting file access
     */
    case Delete = 5;
    /**
     * Moving file access.
     */
    case Move = 6;

    public function toString(): string
    {

        return match( $this )
        {
            self::Read,
            self::Write,
            self::Create,
            self::Delete,
            self::Move      => \mb_strtolower( $this->name ),
            self::ReadWrite => 'read and write',
            default         => 'unknown access'
        };

    }
}