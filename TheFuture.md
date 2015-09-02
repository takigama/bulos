# Introduction #

The following describes how I envisage BULOS OS's functioning within a "real" set of infrastructure. This page takes the form of an example.


# Details #

Part of the purpose of this page is to remind myself of the way I want BULOS to work and how that may introduce challenges, so fear not if it makes little sense to you. But one thing to remember is that the utility OS's are going to be capable of booting off almost anything x86 based.

In the future, I'm hoping BULOS will have the following:
  * A central repository of BULOS utility OS's.
  * A network management utility (providing dhcp, dns, tftp)
  * A network authentication utility (LDAP, for eg).
  * A network file server utility (NFS/iSCSI/etc)
  * A machine reporter (just boots up and tells the BULOS infrastructure about hardware that has been plugged in but not assigned).
  * A BULOS virtualisation engine (KVM for example)
  * some other forms of utility OS's
  * The ability to hook BULOS OS's into one another.

So you start up your laptop, install the BULOS builder and play with it. The first thing you do is browse the global repository looking for what utility OS's you can find. You have some spare piece of hardware floating around so you plug it all into a switch connected to your laptop in some fashion, you use BULOS to build a network management utility and burn it onto CDROM.

From the console of the machine now running the network management utility you give it an IP address and start managing it from the web.

From the web you tell the network management utility where it can get utility OS's from (i.e. your laptop) and so it downloads the available kernel/initrd packages (by now you have probably downloaded and built a number of utility OS's that sound interesting).

The rest of the hardware boots via PXE and downloads the machine reporter which simply tells BULOS about hardware that is plugged in, booting, but not yet assigned  to any task.

From the web control of your network management utility you assign all the rest of the hardware to boot the KVM virtualized environment and suddenly you have a virtual infrastructure as well as a physical one.

From your virtual controls you create several machines (and assign them disk/network) and they also boot the reporter. From the network utility you then move the network utility itself into one of these virtual environments

Next you tell BULOS to boot the original piece of hardware (with a bunch of disk) to boot a network file server. The BULOS infrastructure knows what "storage" is and thus allows you to create new BULOS's and just "ask" for disk from the storage server rather then creating it and assigning it to your machines (think about that one for a bit, it does make sence).

So you then carve off a web server utility, a network monitoring utility (nagios, hobbit, whatever), and a mail server (zimbra perhaps). All of which are still being controlled through the BULOS web gui.

Soon you realise that anyone can login to your utility management because theres no authentication and so you provision off a network authentication service utility and tell BULOS to use that for management purposes.

You carve off a few more virtual machines running some other generic OS (windows/BSD/Solaris) that runs a specific utility someone else needs and cant be provided as a utility itself.

You then realise you want a wiki for internal project work and since none exist within the BULOS repository you create your own and publish it back to the repository.

Later you decide to use SAN connections and plug those into all of your server. You tell the storage utility to move the data its currently serving into there and now your machines start pulling their LUN's directory from the SAN rather then through your storage server.

You then turn the machine that was your storage server into another virtual machine and so forth...