sudo chmod -v 0777 cache
cd cache
mkdir -vp smarty/{cache,compile,configs}
sudo find . -type d | xargs sudo chmod -v 0777
sudo find . -type f | xargs sudo rm -v
git checkout .
