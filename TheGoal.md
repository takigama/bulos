# Introduction #

The following page describes what BULOS has set out to achieve


# Details #

The goal of BULOS is to be able to create OS's that focus on providing a single service and a means of managing that service with as little problem as possible. To achieve that goal, BULOS is actually two components, the builder and the API.

The API is again two components, a web "presentation" layer and a soap service which allows you to manage the platform. The basic API will have functionality to control authentication, network interfaces, disk and so forth (basic's of an OS as such). While the API will also allow you to easily extend to control the utility your producing (such as a file server).

The long term goal is to have BULOS OS's enter some form of cluster so you can produce, manage and deploy new utilities rapidly and from a single web page (part of the reason that the API is split into a presentation layer and a control layer).