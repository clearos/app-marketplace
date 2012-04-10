
Name: app-marketplace
Epoch: 1
Version: 1.0.14
Release: 1%{dist}
Summary: Marketplace
License: Proprietary
Group: ClearOS/Apps
Source: %{name}-%{version}.tar.gz
Buildarch: noarch
Requires: %{name}-core = 1:%{version}-%{release}
Requires: app-base
Requires: app-network
Requires: app-registration

%description
ClearCenter's Marketplace is where you can add new apps and services to your system.  Apps available in the Marketplace have gone through a stringent quality control process by ClearCenter to ensure the quality and security of each submission.

%package core
Summary: Marketplace - APIs and install
License: LGPLv3
Group: ClearOS/Libraries
Requires: app-base-core
Requires: app-clearcenter-core
Requires: app-registration-core
Requires: app-base >= 1:1.0.10

%description core
ClearCenter's Marketplace is where you can add new apps and services to your system.  Apps available in the Marketplace have gone through a stringent quality control process by ClearCenter to ensure the quality and security of each submission.

This package provides the core API and libraries.

%prep
%setup -q
%build

%install
mkdir -p -m 755 %{buildroot}/usr/clearos/apps/marketplace
cp -r * %{buildroot}/usr/clearos/apps/marketplace/

install -d -m 755 %{buildroot}/var/clearos/marketplace
install -D -m 0644 packaging/marketplace.acl %{buildroot}/var/clearos/base/access_control/authenticated/marketplace
install -D -m 0644 packaging/marketplace.conf %{buildroot}/etc/clearos/marketplace.conf

%post
logger -p local6.notice -t installer 'app-marketplace - installing'

%post core
logger -p local6.notice -t installer 'app-marketplace-core - installing'

if [ $1 -eq 1 ]; then
    [ -x /usr/clearos/apps/marketplace/deploy/install ] && /usr/clearos/apps/marketplace/deploy/install
fi

[ -x /usr/clearos/apps/marketplace/deploy/upgrade ] && /usr/clearos/apps/marketplace/deploy/upgrade

exit 0

%preun
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-marketplace - uninstalling'
fi

%preun core
if [ $1 -eq 0 ]; then
    logger -p local6.notice -t installer 'app-marketplace-core - uninstalling'
    [ -x /usr/clearos/apps/marketplace/deploy/uninstall ] && /usr/clearos/apps/marketplace/deploy/uninstall
fi

exit 0

%files
%defattr(-,root,root)
/usr/clearos/apps/marketplace/controllers
/usr/clearos/apps/marketplace/htdocs
/usr/clearos/apps/marketplace/views

%files core
%defattr(-,root,root)
%exclude /usr/clearos/apps/marketplace/packaging
%exclude /usr/clearos/apps/marketplace/tests
%dir /usr/clearos/apps/marketplace
%dir %attr(755,webconfig,webconfig) /var/clearos/marketplace
/usr/clearos/apps/marketplace/deploy
/usr/clearos/apps/marketplace/language
/usr/clearos/apps/marketplace/libraries
/var/clearos/base/access_control/authenticated/marketplace
%attr(0644,webconfig,webconfig) %config(noreplace) /etc/clearos/marketplace.conf
