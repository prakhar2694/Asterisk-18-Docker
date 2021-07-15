#!/bin/bash
#set -vx
mversion="V14.2"
mbuild="624"
NO_GUI=0
NO_UID=0
DEFUSER=asterisk
DESTINATION=""
export LANG=C

usage(){
    echo "Usage: ./install.sh [-d DIR ] [-u|-g] [-U user]"
    echo "  -d : install directory default is root /"
    echo "  -g : do not install gui modules"
    echo "  -u : do not generate uid.txt"
    echo "  -U : user for files permissions"
    echo "  -h : this help"
    echo
    exit 0
}

#args : 1=cmd, 2=iteration, 3=modulo, 4=sleep,5=error
waituntil(){
  nxtwait=0
  #eval is needed
  until eval $1 >/dev/null 2>&1 || [ $nxtwait -eq $2 ];do
   #do not dot all iterations, bit modulo $3
   if [ $(($nxtwait%$3)) -eq 0 ]; then
     echo -n '.'
   fi
   sleep $4
   nxtwait=$((nxtwait+1))
  done
  #limit reached, so error
  if [ $nxtwait == $2 ]; then
    echo $5
    exit 1
  fi
}


# Check args
while [[ $# > 0 ]]
do
    key="$1"

    case $key in
        -d)
          shift
          DESTINATION=$1
          if [ ! -d "$DESTINATION" ];then
            echo "Directory not found : $DESTINATION"
            exit 1
          fi
          echo "Custom install directory : $DESTINATION"
          ;;
        -g|--no-gui)
          NO_GUI=1
          ;;
        -u|--no-uid)
          NO_UID=1
          ;;
        -U)
          shift
          if  id -u "$1" &>/dev/null; then
            DEFUSER=$1
            echo "Custom user : '$DEFUSER'"
          else
            echo "User '$1' do not exists"
            exit 1
          fi
          ;;
        -h|-?|--help)
          usage
          ;;
        *)
          echo "unknown arg: $key"
          exit 1
          ;;
    esac
    shift 
done


src=.
dst=$DESTINATION

#Check SELinux enabled ?
if sestatus -b 2>/dev/null | grep -q enabled; then
  echo "SELinux is enabled: this install won't work"
  echo "Please disable it before install"
  exit 1
fi

#Found Asterisk
if ! command -v asterisk >/dev/null; then
  echo "Asterisk binary not found in PATH:$PATH"
  exit 1
fi

#Check Asterisk Version
# Asterisk normal : Asterisk X.X.X // certified : Asterisk certified/NN.NN-certNN"
asteriskversion=`asterisk -V|grep Asterisk| cut -d ' ' -f 2` 

#Asterisk Versions parsing cert or not
eval `echo $asteriskversion |  sed -n 's_\([0-9]*\)\.\([0-9]*\)\.\([0-9]*\).*_ astver1="\1" astver2="\2" astver3="\3"_p'`
eval `echo $asteriskversion |  sed -n 's_certified/\([0-9]*\)\.\([0-9]*\)-cert\([0-9]*\).*_ astver1="\1" astver2="\2" astcert="\3"_p'`
if [ -z "$astcert" ]; then
  echo "Current Asterisk: '$astver1.$astver2.$astver3.'"
  certf=""
else
  echo "Current Certified Asterisk: '$astver1.$astver2-cert$astcert'"
  certf="cert"
fi

case "${astver1}.${astver2}.${astver3}" in
  1.4.*)
    astflavour="1.4";;
  1.6.2.*)
    astflavour="1.6.2";;
  1.8.*)
    astflavour="1.8";;
  11.*)
    astflavour="11";;
  13.*)
    astflavour="13";;
  14.*)
    astflavour="14";;
  15.*)
    astflavour="15";;
  16.*)
    astflavour="16";;
  *)
    echo "Unknow Asterisk Version: '${astver1}.${astver2}.${astver3}', exiting"
    exit 1;;
esac

#try to find compatible module, with or without cert
if [ -z "$astcert"  ] ; then
  voxmod=`ls $src/modules/app_voximal.so.asterisk_v${astflavour}* 2>/dev/null | grep -Ev 'cert|videocaps'`
else
  voxmod=`ls $src/modules/app_voximal.so.asterisk_v${astflavour}* 2>/dev/null | grep cert`
fi

if [ -f $voxmod ];then
  echo -n "Compatible module found : "
  basename $voxmod
else
  echo "No modules for this asterisk Version: '$astflavour${certf}', exiting"
  exit 1
fi


#echo "Asterisk $asteriskversion installed."

#Check Asterisk Modules Directory
modulesdir=usr/lib/asterisk/modules
if [ ! -d  $dst/$modulesdir ];then
  #try also 64
  modulesdir=usr/lib64/asterisk/modules
elif [ ! -d  $dst/$modulesdir ];then
  echo -n "Asterisk modules dir not found (/$modulesdir), please enter one:"
  read modulesdiruser
  [ ! -d $dst/$modulesdiruser ] && echo "$dst/$modulesdiruser not found." && exit 1
  sed -e s_modulesdir=.*_modulesdir=${modulesdiruser}_ uninstall.sh > uninstall.sh2 &&  mv uninstall.sh2 uninstall.sh
  modulesdir=$modulesdiruser
fi
#Check Asterisk Sounds Directory
soundsdir=var/lib/asterisk/sounds
if [ ! -d  $dst/$soundsdir ];then
  echo -n "Asterisk sounds dir not found (/$soundsdir), please enter one:"
  read soundsdiruser
  [ ! -d $dst/$soundsdiruser ] && echo "$dst/$soundsdiruser not found." && exit 1
  sed -e s_soundsdir=.*_soundsdir=${soundsdiruser}_ uninstall.sh > uninstall.sh2 &&  mv uninstall.sh2 uninstall.sh
  soundsdir=$soundsdiruser

fi

#Check Linux CPU
linuxmachine=`uname -m`

#Check GUI/FreePBX : mysql and asterisk must be up and running :
if [ "$NO_GUI" == "0" ];then

  #Found amportal or fwconsole (FPBX 12 or 13)
  if command -v fwconsole >/dev/null 2>&1; then
    FPBXR="fwconsole --no-ansi reload"
    FPBXMA="fwconsole --no-ansi ma"
  elif command -v amportal >/dev/null 2>&1; then
    FPBXR="amportal a reload"
    FPBXMA="amportal a ma"
  else
    #Not installing any GUI module
    NO_GUI=1
  fi

  #Check if installed freepbx is working
  if [ -f /var/www/html/admin/modules/core/module.xml ] && [ "$NO_GUI" == "0" ]; then
    #Check some bases for FreePBX install
    waituntil "asterisk -rx 'core show version'" "590" "5" "0.1" "Asterisk must be running to install freepbx module, exiting"
    waituntil "$FPBXMA list 2>&1|grep core|grep -q Enabled" "100" "5" "0.1" "$FPBXMA not responding, exiting"
  fi

fi

echo "--- Voximal $mversion Installation ---"

# Copy files

perm_dir=775
perm_files=664
perm_exec=775

echo "Creating directories"
mkdir -p $dst/usr/bin $dst/usr/sbin
install -m $perm_dir -d \
  $dst/usr/lib/voximal \
  $dst/var/lib/voximal \
  $dst/var/cache/voximal \
  $dst/var/log/voximal \
  $dst/var/run/voximal \
  $dst/usr/share/voximal

#Set or not uid file
if [ "$NO_UID" == "0" ];then
  touch $dst/var/lib/voximal/uid.txt
fi

echo "Installing binaries"
install -m $perm_dir $src/bin/voximald $dst/usr/sbin/
install -m $perm_dir $src/bin/voximalc $dst/usr/sbin/
install -m $perm_dir $src/bin/voximalv $dst/usr/sbin/
install -m $perm_dir $src/bin/text2wav $dst/usr/sbin/

echo "Installing VoiceXML examples"
cat <<EOF >$dst/usr/share/voximal/helloworld.vxml
<?xml version="1.0"?>
<vxml version="2.0" xmlns="http://www.w3.org/2001/vxml" xml:lang="en-US">
 <form>
  <block>
    <var name="caller" expr="session.connection.remote.uri"/>
    <var name="called" expr="session.connection.local.uri"/>
    <var name="id" expr="telephone.id"/>
    <var name="param" expr="telephone.param"/>
    <prompt>
    Welcome.
    I am Voximal.
    Your caller number is : <value expr="caller"/>.
    You are calling the : <value expr="called"/>.
    Goodbye.
    </prompt>
  </block>
 </form>
</vxml>
EOF
cat <<EOF >$dst/usr/share/voximal/parrot.vxml
<?xml version="1.0"?>
<vxml version="2.0" xmlns="http://www.w3.org/2001/vxml" xml:lang="en-US">
  <property name="inputmodes" value="dtmf voice"/>
  <property name="interdigittimeout" value="900ms"/>
  <property name="timeout" value="10s"/>
  <property name="confidencelevel" value="0.3"/>
  <property name="completetimeout" value="5s"/>
  <form id="main">
    <field name="text" type="text">
      <prompt bargein="true">Please, say something:</prompt>
      <!-- Bargein test : Change next cond to true, to enable bargein a longer test prompt -->
      <prompt cond="false" bargein="true">You can speak over this prompt to interrupt me with bargein mode enabled, please say something...</prompt>
      <filled>
        <prompt>You said:</prompt>
        <prompt><value expr="text"/></prompt>
        <clear namelist="text"/>
      </filled>
    </field>
  </form>
</vxml>
EOF

if test ! -f $dst/etc/asterisk/voximal.conf ; then
  #New install
  echo "Installing default voximal.conf"
  install -m $perm_files $src/etc/voximal.conf $dst/etc/asterisk/voximal.conf
  if command -v wget >/dev/null 2>&1;then
    #enable local TTS if present and wget installed to check
    if wget -q -O /dev/null 'http://localhost/tts/pico/tts.php?text=h';then
      #enable local settings for pico
      echo "Detected local TTS : pico"
      sed -i s_ttsf\.voximal\.net_localhost_ $dst/etc/asterisk/voximal.conf
      sed -i s_format\=wav_format\=wav16_  $dst/etc/asterisk/voximal.conf
    fi  
  fi
else
  #Upgrade ?
  echo "Installing default voximal.conf.pkg.new"
  install -m $perm_files $src/etc/voximal.conf $dst/etc/asterisk/voximal.conf.pkg.new
  echo "Do not change config, but backup it just in case"
  cp $dst/etc/asterisk/voximal.conf $dst/etc/asterisk/voximal.conf.bck.`date +%Y%m%d`
fi

echo "Installing libraries"
install -m $perm_files $src/lib/* $dst/usr/lib/voximal/

echo "Installing credentials"
if test -f $src/share/roots.pem ; then
  install -m $perm_files $src/share/roots.pem $dst/usr/share/voximal/
fi

echo "Installing Voximal module for asterisk"
install -m $perm_files $voxmod    $dst/$modulesdir/app_voximal.so

echo "Installing additional sounds"
install -m $perm_files $src/sounds/* $dst/$soundsdir

#If asterisk user exists, we change some permissions
if id -u ${DEFUSER} &>/dev/null; then
  echo "User ${DEFUSER} exists, set permissions"
  chown -R -f ${DEFUSER}: \
         $dst/var/lib/voximal \
         $dst/var/cache/voximal \
         $dst/var/log/voximal \
         $dst/var/lib/voximal/uid.txt \
         $dst/usr/share/voximal \
         $dst/usr/share/voximal/roots.pem \
         $dst/etc/asterisk/voximal.conf \
         $dst/etc/voximald.conf

fi

#Install some GUI modules
if [ "$NO_GUI" == "0" ];then
  if [ -f /var/www/html/admin/modules/core/module.xml ]; then

      #Restart asterisk, and check that voximal app is responding
      echo -n "Restarting asterisk"
      asterisk -rx 'core restart now'  >/dev/null 2>&1
      #Detect true restart, when uptime first string is in seconds, max delay 300*0.1s=59s
      waituntil "asterisk -rx 'core show uptime'|grep -q 'Last reload: [0-9]* second'" "590" "5" "0.1" "Unable to communicate with astersik after restart"
      echo -n ""
      #app_voximal must at least display something (loaded), max delay is 200*0.1=59s
      waituntil "asterisk -rx 'voximal show version'|grep -q 'Version'" "590" "10" "0.1" "Unable to communicate with app_voximal"
      echo ""

      #Upgrade ?
      if [ -d $dst/var/www/html/admin/modules/voximal ];then
        echo "Disable old module"
        #Delete old module
        ($FPBXMA disable voximal >/dev/null 2>&1)
        # uninstall will drop tables, links with extension(id) are not refelcted in voximal.conf
        #($FPBXMA uninstall voximal >/dev/null 2>&1)
        #($FPBXMA delete voximal >/dev/null 2>&1)
      else
        echo "New install, adding VoiceXML examples"
        #New install : add samples
        mkdir -p $dst/var/www/html/vxml
        cp $dst/usr/share/voximal/helloworld.vxml $dst/var/www/html/vxml/helloworld.vxml
        cp $dst/usr/share/voximal/parrot.vxml $dst/var/www/html/vxml/parrot.vxml
        #Adjust for samples
        cat<<EOF >$dst/etc/asterisk/extensions_custom.conf
; Allows to dial Numbers from internal too
[from-internal-custom]
include => ext-did
EOF
      fi


      touch $dst/etc/asterisk/voximal_general_custom.conf
      touch $dst/etc/asterisk/voximal_prompt_custom.conf
      touch $dst/etc/asterisk/voximal_recognize_custom.conf
      touch $dst/etc/asterisk/voximal_interpreter_custom.conf
      touch $dst/etc/asterisk/voximal_accounts_custom.conf
      #Set permissons
      chown -f asterisk: $dst/var/www/html/vxml/helloworld.vxml \
                         $dst/var/www/html/vxml/parrot.vxml \
                         $dst/etc/asterisk/extensions_custom.conf \
                         $dst/etc/asterisk/voximal_general_custom.conf \
                         $dst/etc/asterisk/voximal_prompt_custom.conf \
                         $dst/etc/asterisk/voximal_recognize_custom.conf \
                         $dst/etc/asterisk/voximal_interpreter_custom.conf \
                         $dst/etc/asterisk/voximal_accounts_custom.conf

      #IssabelPBX or FreePBX ?
      if cat /var/www/html/admin/modules/framework/module.xml|grep -q IssabelPBX; then
        echo "Installing Voximal IssabelPBX module"
        cp -r $src/www/issabel/voximal $dst/var/www/html/admin/modules
      else
        echo "Installing Voximal FreePBX module"
        cp -r $src/www/freepbx/admin/modules/voximal $dst/var/www/html/admin/modules
      fi
      chown -R asterisk: $dst/var/www/html/admin/modules/voximal $dst/var/www/html/vxml

      echo "Configuring Voximal module"
      ($FPBXMA install voximal) 2>/dev/null
      ($FPBXMA enable voximal) 2>/dev/null
      ($FPBXR) 2>/dev/null
  fi
fi

echo "--- Voximal $mversion installation has finished ---"

