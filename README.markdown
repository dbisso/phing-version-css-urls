## About

This Phing filter takes a fileset of CSS files and looks for urls and adds timestamps reflecting the modification time of the resource.

## Usage

Here is an example target

	<target name="version-css-urls">
		<reflexive>
			<fileset dir="css">
			    <include name="**/*.css"/>
			</fileset>
			<filterchain>
		 		<filterreader classname="VersionCSSURLs"/>
			</filterchain>
		</reflexive>
	</target>

