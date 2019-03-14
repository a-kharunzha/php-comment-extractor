<?php declare(strict_types=1);

namespace App\Console\Command;

use App\PhpParser\CollectionFileDumper;
use App\PhpParser\CommentCollection;
use App\PhpParser\CommentCollector;
use App\PhpParser\CollectionDumper;
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
use Symfony\Component\Finder\Finder;
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
    /** @var NodeTraverser */
    private $nodeTraverser;
    /** @var Parser */
    private $parser;
    /** @var CommentCollector */
    private $collector;

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
        $this->outputPath = $this->prepareOutputPath($input->getArgument('output'), $this->inputPath);
        // objects
        $this->io = new SymfonyStyle($this->input, $this->output);
        $this->nodeTraverser = new NodeTraverser();
        $lexer = new Lexer(array(
            'usedAttributes' => array(
                'comments', 'startLine', 'endLine','startTokenPos','endTokenPos'
            )
        ));
        $this->parser = (new ParserFactory)->create(ParserFactory::PREFER_PHP7, $lexer);
        $this->collector = new CommentCollector();
        $this->nodeTraverser->addVisitor($this->collector);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io->writeln('Path to process: '.$this->inputPath);
        $this->io->writeln('Path to result: '.$this->outputPath);
        /*
        @todo: где-то тут нужно будет ввести опции, определяющие, нужно ли писать в один общий файл, или в пачку отдельных
        */
        $dumper = new CollectionFileDumper($this->outputPath);
        $this->handlePath($this->inputPath,$dumper);
        return 0;
    }

    /**
     * обрабатывает все файды по указанному пути, неважно директории или файла. Дампит результат
     * @param string $inputPath
     *
     * @param CollectionDumper $dumper
     *
     * @return void
     */
    private function handlePath(string $inputPath,CollectionDumper $dumper) : void
    {
        $filesToProcess = [];
        if(is_file($inputPath)){
            $filesToProcess[] = $inputPath;
        }else{
            $finder = new Finder();
            $finder
                ->files()
                ->name(['*.php','*.html'])
                ->in($inputPath)
            ;
            foreach($finder as $file){
                $filesToProcess[] = $file->getRealPath();
            }
        }
        // обрабатываем файлы и сразу выбрасываем ответ переденным дампером
        foreach ($filesToProcess as $filePath) {
            $collection = $this->handleFile($filePath);
            $dumper->dump($collection);
            // поюзал память - почисти
            unset($collection);
        }
    }

    /**
     * собирает из файла все комментарии, отдает в виде коллекции
     *
     * @param string $inputFilePath
     *
     * @return CommentCollection
     */
    function handleFile(string $inputFilePath) : CommentCollection{
        $collection = new CommentCollection($inputFilePath);
        $this->collector->setCollection($collection);
        $code = file_get_contents($inputFilePath);
        // dump($code);
        try {
            $ast = $this->parser->parse($code);
            $this->nodeTraverser->traverse($ast);
        } catch (ParserError $error) {
            echo "Parse error: {$error->getMessage()}\n";
        }
        // отцепляем коллекцию от коллектора, чтобы ее потом после использования можно было дропнуть из памяти
        $this->collector->unsetCollection();
        return $collection;
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

    /**
     * генерирует на основе входных аргументов полный пусть до файла, куда нужно будет записать результат. Без проверки на существование
     *
     * @param $rawDir
     * @param string $inputPath
     *
     * @return string
     */
    private function prepareOutputPath($rawDir, string $inputPath)
    {
        $rawDir = Path::canonicalize($rawDir);
        $absoluteDir = Path::makeAbsolute($rawDir,getcwd());
        $filename = Path::getFilenameWithoutExtension($inputPath).'.'.self::OUTPUT_FILE_EXT;
        return $absoluteDir.DIRECTORY_SEPARATOR.$filename;
    }
}