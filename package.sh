rm -rf target
mkdir target
cp -r plugin target/recipe-pro
pushd target
rm -rf recipe-pro/bin
rm -rf recipe-pro/tests
zip -r -X recipe-pro.zip recipe-pro
popd
