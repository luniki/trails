require 'rake/clean'

SRC = FileList['lib/*']
CLEAN.include('doc/*')
CLEAN.include('ndoc')

file 'doc' => SRC do
  sh "phpdoc --sourcecode on -t `pwd`/doc -d `pwd`/lib -ti 'trails documentation' -o 'HTML:frames:earthli'"
end


file 'ndoc' => SRC do
  sh "mkdir ndoc"
  sh "NaturalDocs  -i lib -o html doc -p ndoc"
end

task 'test' do
  sh "php test/all_tests.php"
end
