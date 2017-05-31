# Extension Development

## Setup

The basic setup of your project's directory is like this:

    <project>/
    ├─ build/
    ├─ docs/
    ├─ src/
    ├─ tests/
    ├─ <info-files>
    └─ <config-files>
    
### The `build` directory

This directory is used to keep the build artifacts and reports that might be useful in additional steps. It is not tracked with the versioning system.

### The `docs` directory

Here you keep the documentation for your extension.
The internal structure depends on how you organise it, and which tools you want to use for publishing.

> A good tool for publishing is [Bookdown](http://bookdown.io/). Bookdown generates DocBook-like HTML output using CommonMark (a variation of Markdown) and JSON files instead of XML. Bookdown is especially well-suited for publishing project documentation to GitHub Pages. Bookdown can be used as a static site generator, or as a way to publish static pages as a subdirectory in an existing site. See also the [Docker Bookdown image with a collection of templates](https://hub.docker.com/r/sandrokeil/bookdown/).

### The `src` directory

There are two different philosophies in the wild on how the `src` directory should be organised, the _package_ layout and the _runtime_ layout. Both have their pros and cons.

#### Package Layout

The source code has the structure as needed for the distribution package, for example

    src/
    ├─ admin/
    ├─ languages/
    ├─ media/
    ├─ site/
    └─ manifest.xml

While this layout makes packaging easier, it is harder to use it for integration tests.
    
#### Runtime Layout

The runtime layout uses the same directory structure as the targeted Joomla! version, for example

    src/
    ├─ administrator/
    │  ├─ components/
    │  └─ languages/
    ├─ components/
    ├─ languages/
    └─ media/

This layout allows you to embed your extension in a Joomla! installation during development. While this layout makes packaging harder, it is easier to use it for integration tests.
    
### The `tests` directory

The layout of the tests directory depends on the testing tool(s) you are using. In any case, you should differentiate at least three types of tests:

- **Unit tests:**
  Tests that do not need a particular setup.
- **Integration tests (edge-to-edge):**
  Tests that need access to Joomla classes, and maybe a database (preferably SQLite, as it does not need extra installation steps)
- **Acceptance tests (end-to-end):**
  Tests that need the complete stack, including HTTP.
  
### Information Files

These are files like `README.md`, `CONTRIBUTING`, `CHANGELOG.txt` and so on. They contain information about the project.
 
### Configuration Files

Usually your project will utilise a couple of tools, which need configuration. Those configuration files belong to this group, e.g., `.gitignore`, `composer.json`, `codeception.yml`, `phpunit.xml`, or even `Robofile`.

## Testing

### Unit Tests

Unit tests are tests that do not need a particular setup. They can be run in your development environment directly, without a Joomla installation. So in this context, _unit_ does not mean maximally isolated code pieces, but any testable combination. The advantage of keeping unit as small as possible is that you can locate problems more precisely, but you might have to use a lot of mocks and stubs.

However, running the tests in your development environment will only cover one PHP version, the one your using on your development machine. 

#### Basic Solution

For each PHP version you want to test with,
- create a PHP container
- copy your project to the container
- add a script that
    - initialises the workspace, e.g., runs `composer install`
    - runs the tests
- collect and merge the coverage reports.

Here's a rough, unelaborated sketch of what a `Dockerfile` for a suitable image could look like:

```dockerfile
FROM php:${PHP_VERSION}

# Install Composer and XDebug
RUN curl -sS https://getcomposer.org/installer | php \
    && mv composer.phar /usr/bin/composer \
    && pecl channel-update pecl.php.net \
    && pecl install xdebug-${XDEBUG_VERSION} \
    && docker-php-ext-enable xdebug

COPY . /app
WORKDIR /app 

RUN composer install --prefer-source --no-interaction

ENV PATH="~/.composer/vendor/bin:./vendor/bin:${PATH}"

CMD ['run-sript.sh']
```

> A predefined image prepared with Composer and XDebug could be provided.

After running the container, it should not be removed automatically (i.e., don't use the `--rm` option), since the coverage data needs to be extracted. The test runner should have been configured to leave its artifacts in `/app/build`, so the results can be pulled with something like
 
```bash
$ docker cp test_container_1:/app/build build/unit_1
```

Then the container can be removed.

```bash
$ docker rm test_container_1
```

#### Parallelisation

One single command should be used to setup, execute, and cleanup after the parallel tests.

##### How it works

A command container (see Appendix) is created with the following setup:

- A _Sequencer_ puts the execution commands for each test class into a _CommandQueue_. The configuration should provide information about which test framework to use (usually one of PHPUnit or Codeception) for which subset of tests.
- The _CommandQueue_ provides each test environment with each command.
- For each test environment, a _Dispatcher_ receives the commands and delegates them to _Executor_ instances, one by one. If there are more commands than runners, the next free runner receives the next command.
- An _Executor_ receives one command at a time and sends its results (f.x. coverage reports) to a _Reporter_.
- The _Reporter_ merges the artifacts, and stores the consolidated reports in the `build` directory. 

#### Interfaces

**Sequencer**

```php
namespace Joomla\Virtualisation\Test;

interface SequencerInterface
{
}
```

**Command**

```php
namespace Joomla\Virtualisation\Test;

interface CommandInterface
{
    public function getRunner(): string;
    public function getTest(): string;
}
```

**CommandQueue**

```php
namespace Joomla\Virtualisation\Test;

interface CommandQueueInterface
{
    public function enqueue(Command $command): void;
    public function registerEnvironment(Dispatcher $environment): void
}
```

**Dispatcher**

```php
namespace Joomla\Virtualisation\Test;

interface DispatcherInterface
{
    public function dispatch(Command $command): void;
    public function registerExecutor(Executor $executor): void
}
```

**Executor**

```php
namespace Joomla\Virtualisation\Test;

use SebastianBergmann\CodeCoverage\CodeCoverage;

interface ExecutorInterface
{
    public function run(Command $command): CodeCoverage;
    public function getLog(): string;
}
```

**Reporter**

```php
namespace Joomla\Virtualisation\Test;

use SebastianBergmann\CodeCoverage\CodeCoverage;

interface ReporterInterface
{
    public function merge(CodeCoverage $coverage): void;
    public function getXml(): string;
}
```

The simplest way to establish communication between the _Executor_ and the container instances is a minimal REST API.

**GET /phpunit/unit/:test**
- URL Parameters:
  - `test`: The path to the test file as needed by the runner relative to `tests/unit`
- Response:
  - `200/OK` on success, with serialised CodeCoverage in the body
  - `500/Internal Server Error` on failure, with execution log in the body

**GET /codecept/unit/:test**
- URL Parameters:
  - `test`: The path to the test file as needed by the runner relative to `tests/unit`
- Response:
  - `200/OK` on success, with serialised CodeCoverage in the body
  - `500/Internal Server Error` on failure, with execution log in the body

# Appendix

## Use a Docker Container as a Command 

Example: `composer`

Define the following function in your `~/.bashrc`, `~/.zshrc` or similar.
You can then run `composer` as if it was installed on your host locally.

```bash
composer () {
    tty=
    tty -s && tty=--tty
    docker run \
        $tty \
        --interactive \
        --rm \
        --user $(id -u):$(id -g) \
        --volume /etc/passwd:/etc/passwd:ro \
        --volume /etc/group:/etc/group:ro \
        --volume $(pwd):/app \
        composer "$@"
}
```

## Copy to Images, Optimise Images

[dkrcp](https://github.com/WhisperingChaos/dkrcp) - Copy files between host's file system, containers, and images.
It supplements `docker cp` by:
   
- Facilitating image creation or adaptation by simply copying files. When copying to an existing image, its state is unaffected, as copy preserves its immutability by creating a new layer.
- Enabling the specification of multiple copy sources, including other images, to improve operational alignment with Linux cp -a and minimize layer creation when TARGET refers to an image.
- Supporting the direct expression of copy semantics where SOURCE and TARGET arguments concurrently refer to containers.

