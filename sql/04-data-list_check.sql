
INSERT INTO `list_check` (`id`, `name`, `description`, `frequency`, `lua`, `m_error`, `m_warn`, `f_root`, `t_add`, `t_upd`) VALUES (3,'unix_tmp_perm','Check the /tmp directory permission',3600,'  function check()\r\n    ls = findBin(\"ls\");\r\n    cmd = ls..\" -ald /tmp\";\r\n    cmd_out = exec(cmd);\r\n    perm = string.sub(cmd_out, 0, 10);\r\n    if perm == \"drwxrwxrwt\" then\r\n       return 0;\r\n    else\r\n       return -2;\r\n    end\r\n  end         ','The permissions of /tmp aren\'t set properly, please check','',0,1346930211,1416318109),(4,'sol_obp_macaddress','Check the local-mac-address EEProm setting',1,'  function check()\r\n    eeprom = findBin(\"eeprom\");\r\n    cmd = eeprom..\" local-mac-address?\";\r\n    cmd_out = exec(cmd);\r\n    flag = string.gsub(cmd_out, \"[%w%?-]+=(%w+)\", \"%1\");\r\n    if flag == \"true\" then\r\n      return 0;\r\n    else\r\n      return -1;\r\n    end\r\n  end','','The local-mac-address EEProm setting is not set to \"true\" as it should.',0,1346931790,1346931790),(5,'sol_obp_autoboot','Check that the auto-boot EEProm setting is set to true',1,'  function check()\r\n    eeprom = findBin(\"eeprom\");\r\n    cmd = eeprom..\" auto-boot?\";\r\n    cmd_out = exec(cmd);\r\n    flag = string.gsub(cmd_out, \"[%w%?-]+=(%w+)\", \"%1\");\r\n    if flag == \"true\" then\r\n      return 0;\r\n    else\r\n      return -1;\r\n    end\r\n  end         ','','The auto-boot EEProm setting is not set to \"true\" as it should',0,1346931856,1346932981),(6,'unix_ntp_sync','Check if the NTP client of the server is in sync',3600,'  function check()\r\n     -- /usr/sbin/ntpdc -pn | grep -c \'^*\'\r\n     ntpdc = findBin(\"ntpdc\");\r\n     grep = findBin(\"grep\");\r\n     cmd = ntpdc..\" -pn | \"..grep..\" -c \'^*\'\";\r\n     cmd_out = exec(cmd);\r\n     if tonumber(cmd_out) > 0 then\r\n       return 0;\r\n     else\r\n       return -1;\r\n     end\r\n  end                  ','','There is no NTP Peers which is in sync',0,1346932779,1416318100),(7,'sol_net_ifaces','Check network interfaces for FAILED',-1,'  function check()\r\n    ifconfig = findBin(\"ifconfig\");\r\n    grep = findBin(\"grep\");\r\n    cmd = ifconfig..\' -a | \'..grep..\' FAILED\';\r\n    if_failed = exec(cmd);\r\n    return if_failed;\r\n  end','A network interface is failed, please check','',0,1349357295,1349357295),(8,'sol_net_dladm','Check For aggr',3600,'function check()\r\n    dladm = findBin(\"dladm\");\r\n    grep = findBin(\"grep\");\r\n    cmd = dladm..\' show-aggr -Lpo sync | \'..grep..\' -c no\';\r\n    if_failed = exec(cmd);\r\n    if tonumber(if_failed) == 0 then\r\n      return 0;\r\n    else\r\n      return -1;\r\n    end\r\n end','Some Interfaces are not in sync','Some Interfaces are not in sync',0,1416318460,1416532052),(10,'sol_check_zpool','Check zpool for failure',3600,'function check()\r\n    zpool = findBin(\"zpool\");\r\n    grep = findBin(\"grep\");\r\n    cmd = zpool..\' list -Ho health | \'..grep..\' -v -c ONLINE\';\r\n    nb = exec(cmd);\r\n    if tonumber(nb) ~= 0 then\r\n      return -2;\r\n    else\r\n      return 0;\r\n    end\r\n end','One or more zpool are DEGRADED!','One or more zpool are DEGRADED!',0,1416530619,1416530668);

