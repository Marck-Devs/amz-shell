#!/bin/bash
installDir=~/.bin/
bashSrc=~/.bashrc
curDir=$(pwd)
fileName=amz-shell
server=0

help(){
    if [ ! -f .help ]; then
    printf "\e[1;36m==========================================================================
                        AMZ-SHELL INSTALL SCRIPT
                                \e[0;35mmarck-devs\e[1;36m
==========================================================================
\e[0m
Install the tool into the current OS.

Take as script folder current: 
        \e[0;33m/home/marck/Projects/amz-shell\e[0m
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++
|                                                       |
|    Usage: sh install.sh [OPTIONS]                     |
|        --server                Intall for servers     |
|        -h, --help              Show this helps        |
|                                                       |
+++++++++++++++++++++++++++++++++++++++++++++++++++++++++

When install with server option, make a dir in the user home, 
.bin folder, and add it to the .bashrc file as path variable

\e[0;33mDefault instalation is for develope mode. Move the script 
to the usr folder. Not recomended to any server, only for locally test.
\e[0;31mNeed root access!
\e[0m"
else
    printf "$(cat .help)"
fi
    echo ""
}

make_file(){
    touch $fileName
    yellow "Dumping code"
    sleep 0.5
    printf "#!/bin/bash\ncd $curDir\nphp amz-shell.php \$@\n" > $fileName 
    green "Add +x option to temp file"
    sleep 0.5
    chmod +x $fileName
}

default_install(){
    usrBin=/usr/local/bin/
    yellow "Install dir: $usrBin"
    sleep 0.5
    cyan "Generating the temp file"
    sleep 0.5
    make_file
    yellow "Moving the scritp to $usrBin"
    if command -v sudo &> /dev/null; then
        sudo mv $fileName $usrBin
        green "Done!"
    elif [ $? -eq 0 ]; then
        mv $fileName $usrBin
        green "Done!"
    else
        red "Need root privilages!"
    fi

}

reset(){
    printf "\e[0m"
}

red(){
    printf "\e[0;31m$1\n"
    reset
}

green(){
    printf "\e[0;32m$1\n"
    reset
}

yellow(){
    printf "\e[1;33m$1\n"
    reset
}

cyan(){
    printf "\e[0;36m$1\n"
    reset
}

server_install(){
    yellow "Install dir: $installDir"
    if [ ! -d $installDir ]; then
        sleep 0.4
        red "$installDir don't exist. It will be created!"
        mkdir -p $installDir
    fi
    sleep 0.5
    cyan "Generating the temp file"
    sleep 0.5
    make_file
    yellow "Moving the scritp to $installDir"
    mv $fileName $installDir
    if [ ! -f $bashSrc ]; then
        touch $bashSrc
    fi
    sleep 0.5
    yellow "Updating $bashSrc"
    printf "\nexport PATH=$installDir:\$PATH\n$(cat $bashSrc)" > $bashSrc
    sleep 0.4
    green "Done!"
}

if [ -n $# ]; then
    case $1 in
    "--help" | "-h")
        help
        ;;
    "--server")
        server_install
        ;;
    *)
        default_install
        ;; 
    esac
fi
