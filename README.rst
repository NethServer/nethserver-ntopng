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
------------------

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

Links
-----

* Official site: http://www.ntop.org/products/ntop/

