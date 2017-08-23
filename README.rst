=================
nethserver-ntopng
=================

After installation, ntopng is disabled by default.  

Ntopng web interface is accessible:

- from specified TCP port (default is ``3000`` with access only from green interfaces)
- from Server Manager using a proxypass: ``https://<server>:980/<alias>`` (see ``alias`` prop)


The software is configured to use a minimal redis instance
named redis-ntopng.

Database Reference
==================

Properties:

- ``Alerts``: can be ``enabled`` or ``disabled``. Default is ``disabled``
- ``Authentication``: can be ``enabled`` or ``disabled``. Default is ``disabled``
- ``Interfaces``: comma-separeted list of monitored interfaces
- ``alias``: auto-generated alias to be used as proxypass URL on httpd-admin


::

 ntopng=service
    Alerts=disabled
    Authentication=disabled
    Interfaces=br0
    TCPPort=3000
    access=green
    alias=4de50f46b8d3e5ec829aba759431b48ad8173768
    status=enabled

Build ntopng on CentOS 7
========================

As root user, make sure to have EPEL repository enabled, then install all required build dependencies: ::

 yum install git automake gcc libtool libpcap-devel libcurl-devel libsqlite3x-devel mariadb-devel gcc-c++ glib2-devel \
 libxml2-devel rrdtool-devel lua-devel python-devel openldap-devel libnetfilter_queue-devel GeoIP-devel rpm-build rpm-sign expect

Create an unprivileged user: ::

 useradd builder

Switch to the newly created user: ::

 su - builder

Clone all repositories: ::

 git clone https://github.com/ntop/ntopng.git -b 3.0-stable
 git clone https://github.com/ntop/nDPI.git -b 2.0-stable

Prepare the RPM environment: ::

 mkdir -p ~/rpmbuild/{BUILD,RPMS,SOURCES,SPECS,SRPMS}
 echo '%_topdir %(echo $HOME)/rpmbuild' > ~/.rpmmacros

Compile all software: ::

 cd nDPI/
 ./autogen.sh && ./configure && make
 cd ../ntopng
 ./autogen.sh && ./configure && make


Build the RPM: ::

 make build-rpm-ntopng-data
 make build-rpm-ntopng


Errors on rpm signing can be ignored if GPG signature is not needed.

Links
=====

* Official site: http://www.ntop.org/products/ntop/

