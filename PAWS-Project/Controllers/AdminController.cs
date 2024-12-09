using System;
using System.Collections.Generic;
using System.Linq;
using System.Web;
using System.Web.Mvc;

namespace PAWSProject.Controllers
{
    public class AdminController : Controller
    {
        // GET: Admin
        public ActionResult Index()
        {
            ViewData["Layout"] = "~/Views/Shared/_AdminLayout.cshtml";
            return View();
        }
    }
}