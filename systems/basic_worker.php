<?php
/**
 * basicWorker Class
 * By Bastian Bringenberg <mail@bastian-bringenberg.de>
 *
 * #########
 * # USAGE #
 * #########
 *
 * See Readme File
 *
 * ###########
 * # Licence #
 * ###########
 *
 * See License File
 *
 * ##############
 * # Repository #
 * ##############
 *
 * Fork me on GitHub
 * https://github.com/bbnetz/FastBackup
 *
 *
 */

/**
 * Class basicWorker
 * @abstract
 * @author Bastian Bringenberg <mail@bastian-bringenberg.de>
 * @link https://github.com/bbnetz/FastBackup
 *
 */
abstract class basic_worker {

	/**
	 * @var string $instancePath the path to the installed service
	 */
	protected $instancePath = '';

	/**
	 * @var string $backupFilename the path to the backupFile
	 */
	protected $backupFilename = '';

	/**
	 * @var string $tmpDir the location where everything 
	 */
	protected $tmpDir = '';

	/**
	 * function __construct
	 * Constructor for all workers
	 *
	 * @param string $instancePath the path to the installed service
	 * @param string $backupFilename the path to the backupfile
	 * @return void
	 */
	public function __construct($instancePath, $backupFilename) {
		$this->instancePath = $instancePath;
		$this->backupFilename = $backupFilename;
		$this->tmpDir = $this->getTmpDir();
	}

	/**
	 * function __destruct
	 * fires when object is removed from memory
	 *
	 * @see http://stackoverflow.com/questions/1407338/a-recursive-remove-directory-function-for-php
	 * @return void
	 */
	public function __destruct() {
		foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($this->tmpDir, FilesystemIterator::SKIP_DOTS), RecursiveIteratorIterator::CHILD_FIRST) as $path)
    		$path->isFile() ? unlink($path->getPathname()) : rmdir($path->getPathname());
    	rmdir($this->tmpDir);
	}

	/**
	 * function run
	 *  
	 *
	 * @return void
	 */
	abstract public function run();

	/**
	 * function getTmpDir
	 * checks for a good temp dir
	 *
	 * @return void
	 */
	protected function getTmpDir() {
		$tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'bb_backup-'.time().DIRECTORY_SEPARATOR;
		if(file_exists($this->tmpDir))
			$tmp = $this->getTmpDir();
		mkdir($tmp);
		return $tmp;
	}

	/**
	 * function writeFinalTar
	 * writes the backup file itself
	 *
	 * @return void
	 */
	protected function writeFinalTar() {
		echo 'Final TAR created.'.PHP_EOL;
		exec('tar cfz '.$this->backupFilename.' '.$this->tmpDir.'*');
	}

	/**
	 * function saveMySQL
	 * Gets informations for a mysql connection and saves in temp directory
	 *
	 * @param string $user the database user
	 * @param string $pass the database password
	 * @param string $db the database itself
	 * @param string $host the database host
	 * @return void 
	 */
	protected function saveMySQL($user, $pass, $db, $host='localhost') {
		echo 'MySQL File created:'.PHP_EOL;
		echo '  User:     '.$user.PHP_EOL;
		echo '  Database: '.$db.PHP_EOL;
		echo '  Host:     '.$host.PHP_EOL;
		exec('mysqldump -u'.$user.' -p'.$pass.' -h'.$host.' '.$db.' > '.$this->tmpDir.'mysql_'.$user.'_'.$db.'.sql');
	}

	/**
	 * function saveFiles
	 * creates a tarball of given files under temp directory
	 *
	 * @param string $path the origin to backup
	 * @param string $title the title for the tar file
	 * @return void
	 */
	protected function saveFiles($path, $title) {
		if(!file_exists($path)) 
			die('Location from '.$path.' is not existing.');
		echo 'Location saved: '.$path.PHP_EOL;
		$tmpFilename = $this->tmpDir.'files_'.$title.'.tar.gz';
		exec('tar cfz '.$tmpFilename.' '.$path);
	}

	/**
	 * function cleanUpComments
	 * cleaning up PHP files from comments so that only real used informations remain
	 *
	 * @param string $string the php files content as string
	 * @return string the cleaned up PHP File
	 */
	protected function cleanUpComments($string) {
		$string = preg_replace('|\#.*|', '', $string);
		$string = preg_replace('/\/\/.*/', '', $string);
		$string = preg_replace('|\/\*\*.*?\*\/|s', '', $string);
		return $string;
	}


}