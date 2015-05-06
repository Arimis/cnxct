"==========================================================
" Description : vimrc for gvim
" Last update : 2013/3/26
" Version : 1.0
"
" Homepage : http://www.recoye.com
" Github : https://github.com/recoye/vimrc
" Email : mail@recoye.com
"
" enjoy:)
"
" =========================================================

" 关闭vi兼容模式
set nocompatible

"风格
if has("gui_running")
    colorscheme desert
else
    colorscheme Tomorrow
endif
if has("win32")
    set guifont=Courier\ New\ 12
else
    set guifont=Monaco:h13
endif

"设置不保存备份
set nobackup

"语法高亮
syntax on

"缩进设置
set tabstop=4
set softtabstop=4
set shiftwidth=4
set autoindent
set expandtab

" 自动缩进
set smartindent

"显示行号
set number

" 搜索
set ignorecase smartcase "忽略大小写
set incsearch  "输入搜索内容时就显示搜索结果
set hlsearch "高亮显示搜索结果

" 错误提示
set noerrorbells "关闭错误提示
set novisualbell "关闭使用可视代替呼叫
set vb t_vb=  "置空错误提示

" 状态栏
set cmdheight=2 "设置命令行为两行
set laststatus=2 "设置状态栏数
set ruler "标尺

" 工具栏
set guioptions-=T "隐藏工具栏
"set guioptions-=m "隐藏菜单栏

" 自动切换目录
set autochdir

" 突出显示当前行
set cursorline

" 不在设定在插入状态无法用退格和delete删除回车符
set backspace=indent,eol,start

"设置字符集
if has("multi_byte")
    " UTF-8编码
    set encoding=utf-8
    set fileencoding=utf-8
    set termencoding=utf-8
    set formatoptions+=mM
    set fencs=utf-8,gbk

    if has("win32")
        source $VIMRUNTIME/delmenu.vim
        source $VIMRUNTIME/menu.vim
        language message zh_CN.utf-8
    endif

endif

" 共享剪切板
set clipboard+=unnamed

" 全屏
if has("win32")
    au GUIEnter * simalt ~x "自动最大化
endif

" 插件
filetype on "检测文件类型
filetype plugin on "载入文件类型插件
" golang 设置
au BufRead,BufNewFile *.go set filetype=go

" 允许未保存时切换缓冲区
set hidden

" 自动填充
inoremap ( ()<ESC>i
inoremap [ []<ESC>i
inoremap " ""<ESC>i
inoremap ' ''<ESC>i
inoremap { {}<ESC>i
inoremap {<CR> {<CR>}<ESC>O
inoremap } <c-r>=ClosePair('}')<CR>
inoremap ) <c-r>=ClosePair(')')<CR>
inoremap ] <c-r>=ClosePair(']')<CR>

function ClosePair(char)
    if getline('.')[col('.')-1] == a:char
        return "\<Right>"
    else
        return a:char
    end if
endfunction

"vim: sw=4"
