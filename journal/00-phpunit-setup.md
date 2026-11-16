# Task 00 – PHPUnit test environment

## Rules (Kurzfassung – this file must be usable stand-alone)

- Working directory – the ONLY place, where anything may be done: `/home/niirrty/Projekte/human/php/Niirrty.IO`
- NEVER push, never touch a remote (`git push`/`git pull`/`git clone`/`gh` forbidden).
- End the task with exactly ONE `git commit`.
- Commit only the files, this task created/changed: `git add <explicit Pfad>` – never `git add .`, `git add -A` or `git commit -a`.
- `HANDOFF.md` and the `journal/` folder are NOT part of the repository → never add them.
- Test files belong to the repository: they stay committed for later reuse.
- Execute this task only if the user explicitly says so.

## Precondition

None (this is the setup task). Current state: PHPUnit is NOT installed (`composer.lock` has empty
`packages-dev`, `vendor/` contains only `niirrty/niirrty.core` 0.6.4). Available: PHP 8.5.12,
Composer 2.11.2. `vendor/autoload.php` already maps `Niirrty\IO\` → `src/` and `Niirrty\` →
`vendor/niirrty/niirrty.core/src` and loads `src/required-code.php` (global constant `IS_WIN`).

## Goal

A reusable PHPUnit environment inside the repository: phpunit installed via composer, a config file
and a bootstrap file, so that every later task (one per class) only adds one test file.

## Steps

1. Add the test dependencies to `composer.json` (mirror the Niirrty.Core package, which is the
   reference project in `vendor/niirrty/niirrty.core/composer.json`):
   ```json
   "require-dev": {
       "phpunit/phpunit": "8.*",
       "mockery/mockery": "1.*"
   }
   ```
   If `phpunit/phpunit` `8.*` does not install/run with PHP 8.5, adjust to the newest major version
   composer can resolve and note the change in the journal file.
2. `composer install` (needs network; vendor/ and composer.lock are gitignored – do not commit them).
3. Create `phpunit.xml.dist` in the project root (adapted from the Niirrty.Core reference):
   ```xml
   <?xml version="1.0" encoding="UTF-8"?>

   <phpunit backupGlobals="false"
            backupStaticAttributes="false"
            colors="true"
            convertErrorsToExceptions="true"
            convertNoticesToExceptions="true"
            convertWarningsToExceptions="true"
            processIsolation="false"
            stopOnFailure="false"
            bootstrap="tests/boot.php">
      <testsuites>
         <testsuite name="Niirrty.IO Test-Suite">
            <directory>./tests</directory>
         </testsuite>
      </testsuites>
      <filter>
         <whitelist>
            <directory>src</directory>
         </whitelist>
      </filter>
   </phpunit>
   ```
   Note: `convert*ToExceptions` attributes are removed/ignored by PHPUnit >= 9 – if such a version is
   installed, keep them away and note it.
4. Create `tests/boot.php` (identical to the reference project):
   ```php
   <?php

   $loader = include dirname( __DIR__ ) . '/vendor/autoload.php';

   $loader->add( 'Niirrty\\Tests', __DIR__ );
   $loader->register();
   ```
   This makes the test namespace `Niirrty\Tests` (incl. `Niirrty\Tests\IO`) resolvable
   from `tests/` (PSR-4-like: `Niirrty\Tests\IO\FileTest` → `tests/Nirrty/Tests/IO/FileTest.php`).
5. Create the placeholder test `tests/Nirrty/Tests/IO/PhpUnitEnvironmentTest.php`:
   namespace `Niirrty\Tests\IO`, class `PhpUnitEnvironmentTest extends TestCase`, with one method
   `testEnvironment()` asserting `assertTrue( true )` and, to prove the autoload chain,
   `assertInstanceOf( Niirrty\IO\FileNotFoundException::class, ... )` is NOT allowed here – instead
   simply assert `assertTrue( \defined( 'IS_WIN' ) )` (proves `src/required-code.php` gets loaded).
6. Create the (empty) folders `tests/Nirrty/Tests/IO/` and `tests/tmp/` – the latter with a `.gitkeep`
   only if really required; `tests/tmp/` is used by later tasks for temporary file/folder fixtures.
7. Extend `.gitignore` with the line `tests/tmp/` (test artifacts must never be committed).
8. Verify: `vendor/bin/phpunit` must run green (the placeholder test), exit code 0.

## Commit (exactly these files)

```
git add composer.json phpunit.xml.dist .gitignore tests/boot.php tests/Nirrty/Tests/IO/PhpUnitEnvironmentTest.php
git commit -m "test: PHPUnit test environment (phpunit.xml.dist, tests/boot.php) initialized"
```

## Notes

- Do NOT change `src/` code, `changelog.md`, `README.md` or `.idea/` files in this task.
- Everything created here (config + boot.php) is the contract for all later tasks:
  test namespace `Niirrty\Tests\IO`, files in `tests/Nirrty/Tests/IO/`, runner `vendor/bin/phpunit`.

## Execution notes (task 00, executed 2026-11-15, commit 2a054fc)

Everything worked as specified – environment: PHP 8.5.12, Composer 2.11.2.

Deviations / findings (contract for all later tasks!):

1. `composer install` alone refused the new `require-dev` entries (lock file out of date) →
   used `composer update phpunit/phpunit mockery/mockery` so the existing Niirrty.Core lock
   (0.6.4) stayed untouched. Resolved: `phpunit/phpunit` **8.5.57** (constraint `8.*` OK, runs
   fine with PHP 8.5) and `mockery/mockery` **1.6.15**.
2. `phpunit.xml.dist` used exactly as specified; PHPUnit 8.5 still accepts the
   `convert*ToExceptions` attributes (no warning) → they stay.
3. PHPUnit 8.5 does **NOT** provide the global assert functions (`assertTrue()`, …) any more
   (their file is not registered by the phpunit package autoload). Therefore the placeholder
   test uses the TestCase instance methods `$this->assertTrue( … )` – same style as the
   reference project Niirrty.Core. → Later tasks must use `$this->assertXxx()`, never bare
   `assertXxx()`.
4. `tests/tmp/` created but empty → nothing committed for it (git does not track empty dirs;
   later tasks create their fixtures there; the folder is gitignored via the added
   `.gitignore` line `tests/tmp/`).
5. Running phpunit creates an untracked `.phpunit.result.cache` in the project root. Kept
   untracked and NOT added to `.gitignore` (the journal specifies exactly one new line there);
   never `git add` it in later tasks.
6. Result: `vendor/bin/phpunit` → `OK (1 test, 2 assertions)`, exit code 0.
