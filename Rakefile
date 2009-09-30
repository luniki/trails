require 'rake/clean'
require 'rake/contrib/sys'

SRC = FileList['lib/*']
CLEAN.include('doc/*')
CLEAN.include('ndoc')

desc 'Create documentation'
file 'doc' => SRC do
  sh "phpdoc --sourcecode on -t `pwd`/doc -d `pwd`/lib -ti 'trails documentation' -o 'HTML:frames:earthli'"
end

desc 'Run all unit tests'
task 'test' do
  sh "php test/all_tests.php"
end

desc 'Build release'
task 'build' => 'compile' do
  sh "php tools/shrink.php lib/trails-unabridged.php > lib/trails.php"
  sh "wc lib/trails.php"
end

desc 'Compile release'
task 'compile' do
  sh "php lib/src/trails.php > lib/trails-unabridged.php"
end

desc 'Run coverage'
task 'coverage' do
  Sys.indir "test" do
    sh "php coverage.php"
  end
end
