#!/bin/bash

mversion="V14.2"

modulesdir=usr/lib/asterisk/modules
soundsdir=var/lib/asterisk
echo "--- Voximal $mversion Remove ---"

# Copy files

perm_dir=775
perm_files=664
perm_exec=775

src=.
dst=

if asterisk -x 'core show version' >/dev/null 2>&1; then
  echo "Please stop asterisk ..."
  exit 1
fi

#Found amportal or fwconsole (FREEPBX or ISSABEL)
if command -v fwconsole >/dev/null 2>&1; then
    FPBXR="fwconsole --no-ansi reload"
    FPBXMA="fwconsole --no-ansi ma"
elif command -v amportal >/dev/null 2>&1; then
    FPBXR="amportal a reload"
    FPBXMA="amportal a ma"
fi

#Uninstall
if [ -d $dst/var/www/html/admin/modules/voximal ];then
  echo "Clear FreePBX  module"
  #Delete old module
  ($FPBXMA disable voximal >/dev/null 2>&1)
  ($FPBXMA uninstall voximal >/dev/null 2>&1)
  ($FPBXMA delete voximal >/dev/null 2>&1)
  rm -rf $dst/var/www/html/admin/modules/voximal
fi

killall voximald  >/dev/null 2>&1 || :
ipcrm -Q 0x7df >/dev/null 2>&1 || :

echo "Removing binaries..."
rm -f $dst/usr/sbin/voximald
rm -f $dst/usr/sbin/voximalc
rm -f $dst/usr/sbin/vxmlplatform
rm -f $dst/usr/sbin/vxmlaudios

echo "Removing configuration files..."
rm -f $dst/etc/voximald.conf
rm -f $dst/etc/default/voximald_defaults.xml
rm -f $dst/etc/asterisk/voximal.conf
if test ! -f $dst/etc/voximald.conf.sample ; then
rm -f $dst/etc/voximald.conf.sample
fi
if test ! -f $dst/etc/default/voximald_defaults.conf.sample ; then
rm -f $dst/etc/default/voximald_defaults.conf.sample
fi
if test ! -f $dst/etc/asterisk/voximal.conf.sample ; then
rm -f $dst/etc/asterisk/voximal.conf.sample
fi

echo "Removing libraries..."
rm -f $dst/usr/lib/voximal/lib*

echo "Removing modules..."
rm -f $dst/$modulesdir/app_voximal.so

echo "Installing sounds..."
rm -f $dst/$soundsdir/silence.h263
rm -f $dst/$soundsdir/silence.raw

echo "Removing all directories..."
rm -rf $dst/usr/lib/voximal
rm -rf $dst/var/lib/voximal
rm -rf $dst/var/cache/voximal
rm -rf $dst/var/log/voximal

if [ -f /var/www/html/admin/modules/core/module.xml ]; then
    echo "Removing FreePBX Module"
    rm -rf  $dst/var/www/html/admin/modules/vxml
fi




echo "--- Voximal $mversion remove has finished ---"

