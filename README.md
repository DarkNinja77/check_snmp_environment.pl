# check_snmp_environment.pl

2020-05-11: Fixed warning and critical return codes when thresholds are hit.

2020-04-29: Added performance output in "cisco" mode.

2020-04-28: Fixed number of power supplies check in "cisco" mode: ISR G1/G2 routers w/o redundant ps now report ps ok.

2020-04-27: Added warning and critical performance output.

2020-04-22: New fork from the 0.7 version from exchange.nagios.org and modified it to run with Nagios Core 4.4.x.
            Added support for Cisco ISR 4K routers using the ciscoNEW mode; using CISCO-ENTITY-SENSOR-MIB; fixed sensor thresholds.
