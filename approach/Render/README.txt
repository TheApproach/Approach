Various Formats To Support as Node types

Each node type can add properties and control the RenderHead(), RenderCorpus(), and RenderTail() 
pipeline which both render(), stream() and __toString() use. Each node has a $nodes[] array
which are connected references and/or relatives of the node, such as its children by default.

This tree is vaguely priortized. 
Feel free to add uncited formats to this tree and do a pull request.
Feel just as free to organize these formats such that maximal nesting occurs.
	More nesting === More code reuse from parent-to-child classes

If you are asking the community to implement something to enable broad access to an industry/idea, prioritize it.
If you are adding a niche format, please place at the end of your category branch, or an appropriate spot using discretion.

If a format becomes implemented, make as many formats as sensible into children of that format.

+ Stream (abstract)
|
+ Container (no content, just children)
+ Node (string content)
|
├── Node\Keyed (key-value pairs that default render: key="value", controllable separation/terminator/encapsulation characters)
|	├── Attributes (explicitly configured Keyed nodes for HTML/XML attributes/classes)
|	├── JSON could be implemented, but is its own Node\Type already
|	├── DSV (delimiter-separated values)
|	 	├── CSV (explicitly comma-separated values)
|	 	├── TSV (explicitly tab-separated values)
|	 	├── SSV (explicitly space-separated values)
|	 	├── ...
├── XML
	├── HTML (general enough to be XHTML, HTML5, etc.)
	├── SVG
	├── MathML
	├── XSLT
├── JSON
	├── YAML
	├── JSON-LD
	├── BSON
	├── CBOR (Concise Binary Object Representation)
	├── UBJSON (Universal Binary JSON)
	├── MessagePack
	├── Smile
	├── Ion
	├── Avro
	├── Protocol Buffers
	├── FlatBuffers
	├── Cap'n Proto
├── URI / URL
|	├── Web (HTTP, HTTPS, FTP, etc.)
|		├── QR Code
|	├── Filesystem
|		├── Windows
|		├── Unix
|		├── BSD
|		├── Mac
|		├── Linux
|		├── Android
|		├── iOS
|	├── Email
|	├── Phone
|	├── Address
|	├── Credit Card
|	├── Serial Number
|		├── ISBN
|		├── GUID
|		├── UUID
|		├── MAC Address
|		├── IP Address
|		├── CIDR
|		├── UPC
|		├── EAN
├── Shell (sh, bash, zsh, etc. simple, directional, line-based, whitespace-separated, etc.)
	(may actually extend Keyed, shown here in the tree for display purposes)
|	├── Bash
|	├── Zsh
|	├── Redis
|	├── Windows Batch
|		├── Windows Command Prompt
|			├── PowerShell
|	├── LLVM / Clang Linker
		(compiler and assembler arguments do not require special logic vs key-value pairs)
		(a bit too fine-grained, but common enough headaches for developers that it's worth including)
|		├── GCC Linker 
|			├── MinGW / Cygwin Linker 
|		├── MSVC Linker
|		├── GLSL, HLSL, Cg Linkers as one node type
|		├── Sun Linker
|		├── Altera, Xilinx Linker
|		├── NASM, MASM, etc. as one node type	 
├── SQL
|	├── MySQL
|	├── PostgreSQL
|	├── SQLite
|	├── Oracle
|	├── SQL Server
|	├── HBase
|	├── Cassandra
|	├── DynamoDB
├── NoSQL
|	├── Neo4j
|	├── MongoDB
|	├── CouchDB
├── RDF
|	├── OWL
|	├── SPARQL
|	├── Turtle
|	├── N-Triples
|	├── N-Quads
|	├── TriG
|	├── N3
|	├── TriX
|	├── Binary XML
|	├── EXI
|	├── Fast Infoset
|	├── XOP
|	├── XQuery
|	├── XProc
|	├── XForms
|	├── XSL-FO
|	├── XSLT
|	├── XPath
|	├── XQuery (etc)
|	├── YANG
|	├── Zorba


├── Binary
|	├── Media
|		├── BMP
|		├── GIF
|		├── JPEG
|			├── EXIF (Exchangeable Image File Format)
|			├── JFIF (JPEG File Interchange Format)
|			├── MPO (Multi-Picture Object)
|			├── SPIFF (Still Picture Interchange File Format)
|			├── JPS (JPEG Stereo)
|			├── PNS (Progressive Network Stream)
|		├── PNG
|			├── APNG (Animated Portable Network Graphics)
|			├── MNG (Multiple-image Network Graphics)
|		├── TIFF
|			├── BigTIFF
|			├── GeoTIFF
|		├── PSD (Photoshop Document)
|		├── ICO (Windows Icon)
|		├── WebP (Web Picture)
|		├── HEIF (High Efficiency Image File Format)
|		├── DICOM
|		├── NIfTI (Neuroimaging Informatics Technology Initiative)
|		├── Analyze (Analyze images are for medical imaging, but not necessarily neuroimaging)
|		├── Minc (Medical Imaging NetCDF)
|		├── AFNI (Analysis of Functional NeuroImages)
├── Waveform (usually audio, but can be used for other data, l)
|		├── WAV
|		├── AAC
|		├── AC3
|		├── MP3
|		├── MIDI
|		├── Ogg
|			├── Vorbis
|			├── Opus
|			├── Speex
|			├── FLAC
|		├── AIFF
|		├── AU
|		├── WMA
|		├── AMR
|		├── GSM
├── Video Node
	├── Motion JPEG (M-JPEG)
		├── MPEG-4
			├── MPEG-2, MPEG-1 (compatibility layer class for MPEG-4)
			├── H.* (H.264, H.265, etc. differences implemented as dynamics within one node type)
			├── DivX
	├── HEVC (High Efficiency Video Coding)
	├── AV1
	├── VP9, VP8 (WebM)
	├── Videography Node (extra interface/profiles for video editing, etc.)
		├── Theora (Ogg)
		├── Dirac (BBC)
		├── CineForm
		├── DNxHD
		├── ProRes
		├── REDCODE
├── 3D Graphics
|	├── 3D Model
|	 	├── 3D Scene
|	 	├── 3D Animation 
|	 	 		+── GLTF (JSON-based actually, but it's a 3D model format)
|	 	 			+── GLB
|	 	 		+── COLLADA
|				+── STL
|				+── OBJ
|				+── FBX
|				+── ...
|				+── Note: these are all false nodes. They will not be implemented as nodes
|				but rather as encoder/decoder classes that can be used by an animation node.

|			├── 3D Rigging (an animation that follows a 3D model's skeleton)
|				+── BVH
|				+── Character Studio
|				+── Acclaim
|				+── Armature (Blender)
|				+── Note: these are all false nodes. They will not be implemented as nodes
|				but rather as encoder/decoder classes that can be used by a rig node.

|			├── 3D Skinning
|				+── MD5 (Doom 3)
|				+── SMD (Valve)
|				+── Note: these are all false nodes too, they end up image nodes with a special
|				mapping to a 3D model node, which the model handles.
|	├── Scene Graph
|		├── X3D
|		├── VRML
|		├── OpenSceneGraph
|		├── OpenFlight
|
|	├── Shader
|		├── GLSL
|		├── HLSL
|		├── Vulkan
|		├── SPIR-V
|		├── DirectX
|	├── 3D Audio
|		├── Ambisonics
|		├── Binaural
|		├── HRTF
|		├── Auro-3D
|		├── Dolby Atmos
|		├── DTS:X
|		├── MPEG-H
|	├── Point Cloud
|		├── PLY
|		├── XYZ
|		├── LAS
|		├── LAZ
|		├── PTS
|		├── PTX
|	├── 3D Printing
|		├── STL
|		├── OBJ
|		├── G-Code
├── Electronic Hardware Connection Formats (control stepper motors, pneumatics, etc.)
|	├── G-Code
|	├── HPGL
|	├── DXF
|	├── Gerber
|	├── Excellon
|	├── KiCad
|	├── Eagle
|	├── Schematic
|	├── PCB
|	├── Gerber
├── Sensor Data
|	├── GPS
|	├── NMEA
|	├── GPX
|	├── FIT
|	├── TCX
|	├── KML
├── Financial Data
|	├── OFX
|	├── QIF
|	├── QFX
├── Font
|	├── TTF
|	├── OTF
├── Factory Data
|	├── STEP
|	├── IGES
|	├── DXF
|	├── DWG
|	├── STL
├── Vehicle Data
|	├── OBD-II (Car)
|	├── J1939, J1708, J1587, J1850 (Truck)
|	├── ISO
|		├── ISO 9141, ISO 14230, ISO 15765 (CAN)
|		├── ISO 11783, ISO 11784, ISO 11785 (Agriculture)
|		├── ISO 11992, ISO 7638 (Trailer)
|		├── ISO 14229, ISO 14230, ISO 15765 (Diagnostics)
|	├── UDS (Unified Diagnostic Services)
|	├── KWP2000 (Keyword Protocol 2000)
|	├── J2534 (Pass-Thru)
|	├── ODX (Open Diagnostic Data Exchange)
|	├── Device Access
|		├── ECU reprogramming: J2534, ISO 22900, ISO 14229, ISO 14230, ISO 15765
|		├── Bluetooth
|		├── WiFi
|		├── USB
|		├── Ethernet
|		├── CAN (Controller Area Network)
|		├── LIN (Local Interconnect Network)
|		├── FlexRay
|		├── MOST (Media Oriented Systems Transport)
|		├── K-Line (ISO 9141, ISO 14230, ISO 15765)
├── Industrial Data
|	├── OPC UA (Open Platform Communications Unified Architecture), OPC DA, OPC HDA
|	├── MTConnect
|	├── FDI (Field Device Integration)
|	├── FDT (Field Device Tool)+++
|	├── EDDL (Electronic Device Description Language)
|	├── ZVEI (Zentralverband Elektrotechnik- und Elektronikindustrie)
|	├── PLCopen
|	├── IEC 61131-3 (International Electrotechnical Commission)
├── Logistics Data
|	├── EDI (Electronic Data Interchange)
|	├── EDIFACT (Electronic Data Interchange for Administration, Commerce and Transport)
|	├── X12 (ASC X12)
|	├── TRADACOMS (Trading Data Communications)
|	├── ebXML (Electronic Business using eXtensible Markup Language)
|	├── RosettaNet
|	├── ODETTE (Organisation for Data Exchange by Tele-Transmission in Europe)
|	├── VDA (Verband der Automobilindustrie)
├── Legal Data
|	├── LEDES (Legal Electronic Data Exchange Standard)
|	├── UTBMS (Uniform Task-Based Management System)
|	├── EDRM (Reference model + XML, JPG, TIFF, PDF, etc.)
|	├── ABA (American Bar Association)
|	├── NIEM (National Information Exchange Model)
|	├── ECF (Electronic Court Filing)
|	├── ELM (Electronic Litigation Management)
|	├── CMIS (Content Management Interoperability Services)
├── Medical Data 
|	├── DICOM (Digital Imaging and Communications in Medicine)
|	├── HL7 (Health Level Seven)
|	├── FHIR (Fast Healthcare Interoperability Resources)
|	├── IHE (Integrating the Healthcare Enterprise)
|	├── SNOMED CT (Systematized Nomenclature of Medicine Clinical Terms)
|	├── LOINC (Logical Observation Identifiers Names and Codes)
|	├── RxNorm (Clinical Drug)
|	├── ICD (International Classification of Diseases)
|	├── CPT (Current Procedural Terminology)
|	├── NIfTI (Neuroimaging Informatics Technology Initiative)
├── Scientific Data
|	├── NetCDF (Network Common Data Form)
|	├── HDF (Hierarchical Data Format)
|	├── FITS (Flexible Image Transport System)
|	├── CDF (Common Data Format)
|	├── GRIB (GRIdded Binary)
|	├── MOL2 (Molecular 3D Structure)
|	├── PDB (Protein Data Bank)
|	├── SBML (Systems Biology Markup Language)
|	├── FASTA (DNA, RNA, Protein)
|	├── QIIME (Quantitative Insights Into Microbial Ecology)
|	├── BAM (Binary Alignment Map)
|	├── SAM (Sequence Alignment Map)
|	├── VCF (Variant Call Format)
|	├── mzML (Mass Spectrometry Markup Language) and mz family
|	├── NMR-STAR (Nuclear Magnetic Resonance)
|	├── CML (Chemical Markup Language)
|	├── CDXML (ChemDraw)
|	├── JCAMP formats
|	├── PDS (Planetary Data System)
|	├── HDF-EOS (Hierarchical Data Format - Earth Observing System)
|	├── ASTER (Advanced Spaceborne Thermal Emission and Reflection Radiometer)
|	├── MODIS (Moderate Resolution Imaging Spectroradiometer)
|	├── MISR (Multi-angle Imaging SpectroRadiometer)
|	├── AIRS (Atmospheric Infrared Sounder)
|	├── AMSR-E (Advanced Microwave Scanning Radiometer - Earth Observing System)
|	├── CERES (Clouds and the Earth's Radiant Energy System)
├── Accounting Data
|	├── XBRL (eXtensible Business Reporting Language)
|	├── BAI (Bank Administration Institute)
|	├── OFX (Open Financial Exchange)
|	├── FIX (Financial Information eXchange)
|	├── MISMO (Mortgage Industry Standards Maintenance Organization)
|	├── ACORD (Association for Cooperative Operations Research and Development)
|	├── IFX (Interactive Financial eXchange)
├── Formats to digitize non-digital Data (Analog waveforms from oscilloscopes, etc.)
|	├── TDMS (Technical Data Management Streaming)
|	├── LabVIEW
|	├── MATLAB






Old list, in case some are missed
 General Formats
  - JSON
  - YAML
  - XML
  - HTML
  - Markdown
  - CSV / TSV
 
 Exchange Formats
  - RSS
  - Atom
  - RDF
  - JSON-LD
  - BBCode
  - OPML
 
 Image Formats
  - JPEG
  - PNG
  - GIF
  - BMP
  - ICO
  - TIFF
  - WebP
  - SVG
 
 Video Formats
  - WebM
  - MP4
  - MKV
  - M4V
  - MPG
  - AVIF
  - FLV
  - MOV
  - WMV
  - AVI
 
 Audio Formats
  - MP3
  - WAV
  - OGG
  - FLAC
  - AAC
  - WMA
 
 Document Formats
  - PDF
  - DOC
  - DOCX
  - XLS
  - XLSX
  - PPT
  - PPTX
  - ODT
  - ODS
  - ODP
  - RTF
  - TXT
  - EPUB
 
 
 Programming Formats
  - C
  - C++
  - C#
  - PHP
  - Python
  - Java
  - JavaScript
  - CSS
  - LESS
  - SCSS
  - SASS
  - Stylus
  - CoffeeScript
  - TypeScript
  - Textile
  - reStructuredText
  - DocBook
  - MediaWiki
  - DokuWiki
  - Creole
 
 Database Formats
  - SQL (MySQL, PostgreSQL, SQLite, Oracle, SQL Server, etc. including HBases, Cassandra, DynamoDB, etc.)
  - Neo4j
  - MongoDB query
  - CouchDB query
  - Redis commands
  - Elasticsearch / Lucene / Solr query
  - SPARQL
  - Turtle
  - N-Triples
  - N-Quads
  - TriG
  - N3
  - TriX
 
 
 Other Formats
  - Binary
  - Hex
  - Base64
  - URL
  - Email
  - Phone
  - Address
  - Credit Card
  - ISBN
  - GUID
  - UUID
  - MAC Address
  - IP Address
  - CIDR
  - UPC
  - EAN
  - QR Code
  - Bar Code
