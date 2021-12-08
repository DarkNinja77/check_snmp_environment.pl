## Changelog

2021-12-08: Added Cisco Wireless LAN Controller (WLC) support
2021-11-29: Fixed typos.
2020-05-11: Fixed warning and critical return codes when thresholds are hit.
2020-04-29: Added performance output in "cisco" mode.
2020-04-28: Fixed number of power supplies check in "cisco" mode: ISR G1/G2 routers w/o redundant ps now report ps ok.
2020-04-27: Added warning and critical performance output.
2020-04-22: New fork from the 0.7 version from exchange.nagios.org and modified it to run with Nagios Core 4.4.x.
            Added support for Cisco ISR 4K routers using the ciscoNEW mode; using CISCO-ENTITY-SENSOR-MIB; fixed sensor thresholds.


## How to use
### Nagios command definition examples
```
define command {
        command_name    check_environment_cisco_isr4k
        command_line    $USER1$/check_snmp_environment.pl -2 -t 10 -H $HOSTADDRESS$ -C $USER2$ -T ciscoNEW -f -R '^Temp: Inlet( 1)* R0/0$'
}
define command {
        command_name    check_environment_cisco_isr4321
        command_line    $USER1$/check_snmp_environment.pl -2 -t 10 -H $HOSTADDRESS$ -C $USER2$ -T ciscoNEW -f -R 'Temp: Internal'
}
define command {
        command_name    check_environment_cisco_isrG2
        command_line    $USER1$/check_snmp_environment.pl -2 -t 10 -H $HOSTADDRESS$ -C $USER2$ -T cisco -f -R "^Intake$|^Intake (Left|Right)$|^chassis$"
}
define command {
        command_name    check_environment_cisco_switch
        command_line    $USER1$/check_snmp_environment.pl -2 -t 10 -H $HOSTADDRESS$ -C $USER2$ -T cisco -f -R "Inlet Temp Sensor|Sensor#1|Temp [Ss]ensor 0|^Air [Ii]nlet$|Temp: *[Ii]nlet$"
}
define command {
        command_name    check_environment_cisco_wlc
        command_line    $USER1$/check_snmp_environment.pl -2 -t 10 -H $HOSTADDRESS$ -C $USER2$ -f -T ciscoWLC
}
```
