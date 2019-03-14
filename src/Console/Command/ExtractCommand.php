<?php declare(strict_types=1);

namespace App\Console\Command;

use App\PhpParser\CommentCollector;
use App\PhpParser\CommentDumper;
use PhpParser\Error as ParserError;
use PhpParser\Lexer;
use PhpParser\NodeTraverser;
use PhpParser\Parser;
use PhpParser\ParserFactory;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\PathUtil\Path;

class ExtractCommand extends Command
{
    /** @var InputInterface  */
    private $input;
    /** @var OutputInterface */
    private $output;
    /** @var string */
    private $inputPath;
    /** @var string */
    private $outputPath;
    /** @var SymfonyStyle */
    private $io;

    const OUTPUT_FILE_EXT = 'txt';
    const OUTPUT_DIR_RIGHTS = 0644;
    const OUTPUT_FILE_RIGHTS = 0755;
    /** @var NodeTraverser */
    private $nodeTraverser;
    /** @var Parser */
    private $parser;

    protected function configure(): void
    {
        $this
            ->setName('extract')
            ->setDescription('Extracts php comments from given files')
            ->setHelp(<<<'EOF'
<info>php %command.full_name%</info>
EOF
            )
            ->addArgument('input', InputArgument::REQUIRED, 'Path to source dir|file')
            ->addArgument('output', InputArgument::REQUIRED, 'Path to output dir')
            /*
            // не катит, потому что "Options are _always_ optional"
            ->addOption(
                'input',
                'i',
                InputOption::VALUE_REQUIRED,
                'path to source dir|file?'
            )*/
        ;
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // strings
        $this->input = $input;
        $this->output = $output;
        $this->inputPath = $this->makeInputPath($input->getArgument('input'));
        $this->outputPath = $this->makeOutputPath($input->getArgument('output'), $this->inputPath);
        // objects
        $this->io = new SymfonyStyle($this->input, $this->output);
        $this->nodeTraverser = new NodeTraverser();
        $lexer = new Lexer(array(
            'usedAttributes' => array(
                'comments', 'startLine', 'endLine','startTokenPos','endTokenPos'
            )
        ));
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {

        $this->io->writeln('Path to process: '.$this->inputPath);
        $this->io->writeln('Path to result: '.$this->outputPath);
        // пока только один файл
        $collector = $this->handleFile($this->inputPath);
        // dump($collector->getComments());
        (new CommentDumper($collector))
            ->dumpToFile($this->outputPath);
        return 0;
    }



    function handleFile(string $inputFilePath) : CommentCollector{
        $collector = new CommentCollector();
        $this->nodeTraverser->addVisitor($collector );
        $code = file_get_contents($inputFilePath);
        // dump($code);
        try {
            $ast = $this->parser->parse($code);
            $this->nodeTraverser->traverse($ast);
        } catch (ParserError $error) {
            echo "Parse error: {$error->getMessage()}\n";
        }
        $this->nodeTraverser->removeVisitor($collector);
        return $collector;
    }

    /**
     * нормализует и превращает в абсолютный путь до файла для обработки. Проверяет что он существует
     *
     * @param string $getcwd
     * @param string $rawPath
     *
     * @throws \InvalidArgumentException
     * @return string
     */
    private function makeInputPath(string $rawPath)
    {
        // realpath сам резолвит относительные пути
        $realPath = Path::canonicalize($rawPath);
        if(!file_exists($realPath)){
            throw new \InvalidArgumentException('Specified input path does not exists: '.$rawPath);
        }
        return $realPath;
    }

    private function makeOutputPath($rawDir, string $inputPath)
    {
        $rawDir = Path::canonicalize($rawDir);
        $absolute = Path::makeAbsolute($rawDir,getcwd());
        if(!file_exists($rawDir)){
            $this->io->writeln('Creating directory for output: '.$absolute);
            mkdir ($absolute ,self::OUTPUT_DIR_RIGHTS,true);
        }
        $filename = Path::getFilenameWithoutExtension($inputPath).'.'.self::OUTPUT_FILE_EXT;
        return $absolute.DIRECTORY_SEPARATOR.$filename;
    }
}