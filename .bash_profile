alias ll='ls -alF'
#enables colorin the terminal bash shell export
CLICOLOR=1
#sets up thecolor scheme for list export
LSCOLORS=gxfxcxdxbxegedabagacad
export PS1='\[\033[01;32m\]\u@\h\[\033[00m\]:\[\033[01;36m\]\w\[\033[00m\]\$ '
#enables colorfor iTerm
export TERM=xterm-color

export GOPATH=/Users/cfc4n/gopath
export GOROOT=/usr/local/Cellar/go/1.4.2/libexec
export GOBIN=$GOROOT/bin

#export PATH=$PATH:$GOPATH/bin:/Users/cfc4n/Project/golang/z-game/bin:/usr/local/Cellar/go/1.3.3/libexec/bin
export PATH=$PATH:$GOBIN:$GOPATH/bin:/Users/cfc4n/Project/golang/loris_svn/bin
##
# Your previous /Users/cfc4n/.bash_profile file was backed up as /Users/cfc4n/.bash_profile.macports-saved_2015-03-02_at_16:06:20
##

# MacPorts Installer addition on 2015-03-02_at_16:06:20: adding an appropriate PATH variable for use with MacPorts.
export PATH="/opt/local/bin:/opt/local/sbin:$PATH"
# Finished adapting your PATH environment variable for use with MacPorts.

export HISTORY_FILE=/var/log/bash/history-`date '+%Y%m'`.log 

export PROMPT_COMMAND='{ date "+%Y-%m-%d %T ##### $(who am i |awk "{print \$1\" \"\$2\" \"\$5}")  #### $(history 1 | { read x cmd; echo "$cmd"; })"; } >> $HISTORY_FILE'
