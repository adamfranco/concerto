<?

// This is just going to ensure that we haven't added any protected info to
// the layout already. We will start a new execution cycle with everything fresh.

header("Location: ".MYURL."/auth/fail/".implode("/",$harmoni->pathInfoParts));