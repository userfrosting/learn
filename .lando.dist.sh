# grav installer for Lando
gravVersion="1.7.12"
cd ~/
# check for existing grav
echo "Checking Grav"
if [[ $(< grav.txt) == "$gravVersion" ]]; then
    # valid grav version
    echo "Grav $gravVersion installed, no further action required"
    exit
else
    echo "Installing Grav $gravVersion"
    # cleanup existing install if present
    find -path "./grav/*" -not -path "./grav" -not -path "./grav/user/*" -not -path "./grav/user" -delete
    # need to install grav
    wget -q https://getgrav.org/download/core/grav/$gravVersion
    unzip -q $gravVersion -x "grav/user/*"
    rm $gravVersion
    cd grav
    # run install command
    bin/grav install
    # mark as installed
    cd ..
    echo $gravVersion > grav.txt
fi