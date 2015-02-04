## VoiceStat-API ##

**VoiceStat-API** is a API developed for a PBXInAFlash (CentOS) machine


### Features ###

Has several functions such as:

- Service Status
- Service Start
- Service Stop
- Service Restart

### Work around to execute commands ###


**/etc/sudoers**
```
Defaults:asterisk !requireTTY
asterisk ALL=NOPASSWD:/sbin/service
```

**/etc/httpd/conf/httpd.conf**
```
User asterisk
Group asterisk
```

**The machine itself**
```
setenforce 1
service httpd restart
```
