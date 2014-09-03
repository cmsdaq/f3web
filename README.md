f3web
=====

Web Services for F3

<h2>Description</h2>

Provides web services and related software to monitor and control:
<ul>
<li> The ElasticSearch infrastructure of F3
<li> The Operation of the system (including daemon processes running on the servers)
<li> The ancillary software operating as ES plugins
</ul>

Provides also:
<ul>
<li> A query-based inference engine used to diagnose faults
<li> Software to generate and install queries and rules for the said engine
<li> Human interface for the engine
</ul>

<h2>Installation</h2>

The webservice bundle comes as a single tarball. The plugins for ES come as zipfiles for direct installation into the ES cluster(s). Plugin installation is subsequently wrapped in an RPM for use with puppet
