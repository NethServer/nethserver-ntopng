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

- ``Authentication``: can be ``enabled`` or ``disabled``. Default is ``disabled``
- ``Interfaces``: comma-separeted list of monitored interfaces
- ``alias``: auto-generated alias to be used as proxypass URL on httpd-admin


::

 ntopng=service
    Authentication=disabled
    Interfaces=br0
    TCPPort=3000
    access=green
    alias=4de50f46b8d3e5ec829aba759431b48ad8173768
    status=enabled

Download lates upstream release
===============================

To download directly from stable repository: ::

 wget  -r --no-parent -A n2n*rpm -A ndpi*rpm -A ntopng*rpm -A pfring*rpm http://packages.ntop.org/centos/7/x86_64/Packages/

To access the stable repository: ::

 cd /etc/yum.repos.d/
 wget http://packages.ntop.org/centos-stable/ntop.repo -O ntop.repo


See also http://packages.ntop.org/centos-stable/

Links
=====

* Official site: http://www.ntop.org/products/ntop/

