The project originally existed on SourceForge as minifed and is now being moved to google's code site.

The purpose of the project is (as the name suggests) to build utility linux OS's. Originally this all stemmed from a project called iSCSI office that i'd started on back in early 2000 and never got very far with.

The idea behind it is simple, first you grab a list of packages from some pre-defined distribution (for eg fedora, redhat, centos, ubuntu, slackware, etc), create a list of files to delete (for example /usr/share/doc, /usr/share/man), add a "base" package, create some form of control, click "build" and out pops a kernel/initrd combo you can boot from almost anywhere (cdrom, usb, network, etc). The idea being that you'd be able to cram enough smarts into your application to fit entirely in memory and allow for some centralised control over it.

Once you've created a project, you can export it for other people to use and in the future i'm hoping to be able to provide repositories of projects for people to be able to share.