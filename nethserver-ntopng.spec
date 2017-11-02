Summary: NethServer ntopng configuration
Name: nethserver-ntopng
Version: 2.0.1
Release: 1%{?dist}
License: GPL
URL: %{url_prefix}/%{name} 
Source0: %{name}-%{version}.tar.gz
BuildArch: noarch

Requires: ntopng-pcap >= 3
Requires: redis

BuildRequires: perl
BuildRequires: nethserver-devtools 

Obsoletes: nethserver-bandwidthd
Obsoletes: bandwidthd
Obsoletes: ntopng

%description
NethServer ntopng configuration

%prep
%setup

%build
%{makedocs}
perl createlinks
mkdir -p root/var/lib/redis-ntopng

%install
rm -rf %{buildroot}
(cd root; find . -depth -print | cpio -dump %{buildroot})
%{genfilelist} %{buildroot} --dir /var/run/ntopng "%attr(0755,nobody,nobody)"  > %{name}-%{version}-filelist
echo "%doc COPYING" >> %{name}-%{version}-filelist

%post

%preun

%files -f %{name}-%{version}-filelist
%defattr(-,root,root)
%attr(755, nobody, nobody) /var/lib/redis-ntopng
%dir %{_nseventsdir}/%{name}-update


%changelog
* Thu Nov 02 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.1-1
- Report Bandwith usage - could not decode ntopng response - Bug NethServer/dev#5368

* Fri Oct 06 2017 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 2.0.0-1
- Ntopng 3  - NethServer/dev#5341

* Thu Jul 21 2016 Davide Principi <davide.principi@nethesis.it> - 1.4.1-1
- Web UI: missing labels - Bug NethServer/dev#5061 

* Thu Jul 07 2016 Stefano Fancello <stefano.fancello@nethesis.it> - 1.4.0-1
- First NS7 release

* Mon Nov 30 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.1-1
- Specify network interfaces for ntopng - Enhancement #3325 [NethServer]

* Thu Nov 19 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.3.0-1
- Ntopng: optimize redis memory usage - Enhancement #3319 [NethServer]

* Thu Aug 06 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.3-1
- Update ntopng - Enhancement #3225 [NethServer]
- Bandwidth monitor (ntopng) : remove modify password form  from nethserver interface - Enhancement #3172 [NethServer]

* Thu Apr 23 2015 Davide Principi <davide.principi@nethesis.it> - 1.2.2-1
- ntopng listen on alias ip - Bug #3034 [NethServer]

* Fri Apr 10 2015 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.2.1-1
- Ntopng doesn't start - Bug #3110 [NethServer]

* Fri Aug 08 2014 Davide Principi <davide.principi@nethesis.it> - 1.2.0-1.ns6
- Update ntopng to version 1.1 - Enhancement #2506 [NethServer]

* Wed Feb 26 2014 Davide Principi <davide.principi@nethesis.it> - 1.1.2-1.ns6
- Revamp web UI style - Enhancement #2656 [NethServer]

* Wed Feb 05 2014 Davide Principi <davide.principi@nethesis.it> - 1.1.1-1.ns6
- NethCamp 2014 - Task #2618 [NethServer]
- Update all inline help documentation - Task #1780 [NethServer]

* Wed Dec 18 2013 Davide Principi <davide.principi@nethesis.it> - 1.1.0-1.ns6
- Service supervision with Upstart - Feature #2014 [NethServer]

* Thu Aug 01 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.1-1.ns6
- Fix typo in translations

* Thu Aug 01 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1.ns6
- First release #1126

* Wed Jul 31 2013 Giacomo Sanchietti <giacomo.sanchietti@nethesis.it> - 1.0.0-1.ns6
- First release  #1126

