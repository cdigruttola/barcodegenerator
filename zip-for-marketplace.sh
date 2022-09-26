rm -rf tmp
mkdir -p tmp/barcodegenerator
cp -R classes tmp/barcodegenerator
cp -R config tmp/barcodegenerator
cp -R docs tmp/barcodegenerator
cp -R override tmp/barcodegenerator
cp -R sql tmp/barcodegenerator
cp -R src tmp/barcodegenerator
cp -R translations tmp/barcodegenerator
cp -R views tmp/barcodegenerator
cp -R upgrade tmp/barcodegenerator
cp -R vendor tmp/barcodegenerator
cp -R index.php tmp/barcodegenerator
cp -R logo.png tmp/barcodegenerator
cp -R barcodegenerator.php tmp/barcodegenerator
cp -R config.xml tmp/barcodegenerator
cp -R LICENSE tmp/barcodegenerator
cp -R README.md tmp/barcodegenerator
cd tmp && find . -name ".DS_Store" -delete
zip -r barcodegenerator.zip . -x ".*" -x "__MACOSX"
