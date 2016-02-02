sudo chmod -v 0777 cache
cd cache
mkdir -vp smarty/cache
mkdir -vp smarty/compile
mkdir -vp smarty/configs
sudo find . -type d | xargs sudo chmod -v 0777
sudo find . -type f | xargs sudo rm -v
git checkout .
