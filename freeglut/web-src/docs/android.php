<?php
require("../template.php");

# Now set the title of the page:
setPageTitle("Android");

# Make the header.
generateHeader($_SERVER['PHP_SELF']);
?>

<p>

  FreeGLUT 3.0 introduces support for the Android platform.<br />

  This platform is different than traditional desktop platforms, requiring cross-compilation, interfacing with a touchscreen, and ability for your application to be paused and resumed at any time.<br />

  Here's how:

</p>


<ul>
<li><a href="#compiling">Compiling</a></li>
<li><a href="#using">Using in your projects</a></li>
<li><a href="#roadmap">Roadmap</a></li>
<li><a href="#api">New API</a></li>
<li><a href="#notes">Notes</a></li>
<li><a href="#links">Links</a></li>
</ul>


<a name="compiling"></a>
<h1>Compiling</h1>

  <ul>

    <li>Note: at the moment, you need to chose between OpenGL ES 1.0 (FREEGLUT_GLES1) or 2.0 (FREEGLUT_GLES2) at compile time.<br />
    In the near future, we may implement a way to set this at run-time.</li>

    <li>

      Use your own cross-compiler for Android, or export the one from
      the Android NDK:

      <pre>
        /usr/src/android-ndk-r7c/build/tools/make-standalone-toolchain.sh \
          --platform=android-9 \
          --install-dir=/usr/src/ndk-standalone-9
      </pre>

    </li>

    <li>Compile FreeGLUT and install it in your Android cross-compiler
        path:

      <pre>
        PATH=/usr/src/ndk-standalone-9/bin:$PATH
        cd /usr/src/freeglut-x.x/
        mkdir cross-android-gles2/
        cd cross-android-gles2/
        cmake \
          -D CMAKE_TOOLCHAIN_FILE=../android_toolchain.cmake \
          -D CMAKE_INSTALL_PREFIX=/usr/src/ndk-standalone-9/sysroot/usr \
          -D CMAKE_BUILD_TYPE=Debug \
          -D FREEGLUT_GLES2=ON \
          -D FREEGLUT_BUILD_DEMOS=NO \
          ..
        make -j4
        make install
        # Only static for now:
        rm -f /usr/src/ndk-standalone-9/sysroot/usr/lib/libfreeglut-gles?.so*
      </pre>

    </li>

  </ul>

<a name="using"></a>
<h1>Using in your projects</h1>

  <h2>Compile your own project using common build systems</h2>

    <p>For instance if you use the autotools:</p>

    <pre>
      PATH=/usr/src/ndk-standalone-9/bin:$PATH
      export PKG_CONFIG_PATH=/usr/src/ndk-standalone-9/sysroot/usr/share/pkgconfig
      ./configure --host=arm-linux-androideabi --prefix=/somewhere
      make
      make install
    </pre>

    <p>If you use CMake, you may want to copy our Android toolchain
      'android_toolchain.cmake':</p>

    <pre>
      PATH=/usr/src/ndk-standalone-9/bin:$PATH
      export PKG_CONFIG_PATH=/usr/src/ndk-standalone-9/sysroot/usr/share/pkgconfig
      cp .../freeglut-x.x/android_toolchain.cmake .
      mkdir cross-android/
      cd cross-android/
      cmake \
        -D CMAKE_TOOLCHAIN_FILE=../android_toolchain.cmake \
        -D CMAKE_INSTALL_PREFIX=/somewhere \
        -D CMAKE_BUILD_TYPE=Debug \
        -D MY_PROG_OPTION=something ... \
        ..
      make -j4
      make install
    </pre>

    <p>Check <code>progs/test-shapes-gles1/</code> in the FreeGLUT
      source distribution for a complete, stand-alone example.</p>

<h2>Compile your own project using the NDK build-system</h2>

  <ul>

    <li>

      Create a module hierarchy pointing to FreeGLUT, with our Android.mk:

      <pre>
        mkdir freeglut-gles2/
        cp .../freeglut-x.x/android/gles2/Android.mk freeglut-gles2/
        ln -s /usr/src/ndk-standalone-9/sysroot/usr/include freeglut-gles2/include
        ln -s /usr/src/ndk-standalone-9/sysroot/usr/lib freeglut-gles2/lib
      </pre>

    </li>

    <li>

      Reference this module in your jni/Android.mk:

      <pre>
        LOCAL_STATIC_LIBRARIES := ... freeglut-gles2
        ...
        $(call import-module,freeglut-gles2)
      </pre>

    </li>

    <li>

      You now can point your NDK_MODULE_PATH to the directory containing the module:

      <pre>
        ndk-build NDK_MODULE_PATH=.
      </pre>

    </li>

  </ul>


<a name="roadmap"></a>
<h1>Roadmap</h1>

Done:
<ul>
<li>Initialize context with EGL</li>
<li>Keyboard support</li>
<li>Mouse support</li>
<li>Virtual keypad (on touchscreen)</li>
<li>Extract assets in cache dir on start-up</li>
<li>Make EGL support reusable by Mesa X11</li>
<li>freeglut_std.h can be used with GLES1 or GLES2 or non-ES headers<br />
(using -DFREEGLUT_GLES1 and -DFREEGLUT_GLES2)</li>
<li>GLES1 and GLES2 support for geometry</li>
<li>Pause/resume application support</li>
</ul>

TODO:
<ul>
<li>Open new windows (if that's possible)</li>
<li>Joystick support (xperia play...)</li>
<li>Display translucent keys on virtual keypad</li>
<li>API to access raw JVM structure and raw Activity(ies?)
  structure</li>
<li>API to detect touchscreen presence</li>
<li>API (or configuration file?) to disable assets extraction</li>
<li>Callback to reload OpenGL resources lost during a pause</li>
<li>Callback for pause/resume notifications</li>
</ul>

Possibly implemented later:
<ul>
<li>Support for menus and basic fonts</li>
</ul>

<a name="api"></a>
<h1>New API</h1>

New functions will be necessary to :
<ul>
<li>detect touchscreen presence</li>
<li>disable assets extraction</li>
</ul>

(Work In Progress)

<a name="notes"></a>
<h1>Notes</h1>

<ul>

  <li>
    Android never truly kills an application, even when pressing the
    Back button, even when the application
    is <code>onDestroy</code>'d: the process is still running and
    ready to accept <code>onCreate</code> event to become active
    again.<br />

    By default, FreeGLUT <code>exit()</code>s when the last window is
    closed (without returning to your <code>main</code>).  But this
    behavior can be changed
    with <code>glutSetOption(GLUT_ACTION_ON_WINDOW_CLOSE, ...)</code>,
    in which case your have to either <code>exit()</code> yourself at
    the end of your <code>main</code>, or make sure
    your <code>main</code> can be called multiple times (in
    particular: beware of <code>static</code> variables that won't be
    reinitialized).
  </li>

  <li>
    When a key is repeated, down and up events happen most often at
    the exact same time.  This makes it impossible to animate based on
    key press time.<br />

    e.g. down/up/wait/down/up rather than down/wait/down/wait/up<br/>

    This looks like a bug in the Android virtual keyboard system :/
    Real buttons such as the Back button appear to work correctly
    (series of down events with proper getRepeatCount value).<br />

    To work around this, FreeGLUT provides its own minimal virtual
    keypad.  It may be replaced by a virtual (touchscreen) joystick.
  </li>

</ul>

<a name="links"></a>
<h1>Links</h1>

<ul>

  <li>http://pygame.renpy.org/ : Pygame Subset for Android, it
    designed an API for managing Android app lifecycle
    (<code>android.check_pause</code> and
    <code>android.wait_for_resume</code>)</li>

</u>

<?php generateFooter(); ?>
